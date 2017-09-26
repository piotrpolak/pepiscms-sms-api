<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once module_path('crud') . '/libraries/AdminCRUDController.php';

/**
 * sms_api admin controller
 *
 * @author piotr@polak.ro
 * @date 2017-09-01
 */
class Sms_apiAdmin extends AdminCRUDController
{
    /**
     * Default constructor containing all necessary definitions
     */
    public function __construct()
    {
        parent::__construct();

        // Getting module and model name from class name
        $module_name = $this->getModuleName();
        $model_name = $this->getModelName();

        // Loading module language
        $this->load->moduleLanguage($module_name, $module_name);

        // Loading module main model
        $this->load->moduleModel($module_name, $model_name);
        $this->setFeedObject($this->$model_name);

        // Setting labels, please note the convention
        $this->setPageTitle($this->lang->line($module_name . '_module_name'));
        $this->setAddNewItemLabel($this->lang->line($module_name . '_add'));


        $this->setTooltipTextForIndex($this->lang->line($module_name . '_index_tip'));
        $this->setTooltipTextForEdit($this->lang->line($module_name . '_edit_tip'));

        // Setting crud properties, these are optional. Default TRUE all
        $this->setDeletable(FALSE);
        $this->setAddable(TRUE);
        $this->setEditable(FALSE);
        $this->setPreviewable(TRUE);
        $this->setPopupEnabled(FALSE);
        $this->setOrderable(FALSE);

        $this->setExportable(TRUE, function ($resulting_line) {
            unset($resulting_line['id']);
            return $resulting_line;
        });


        $this->setMetaOrderField('id', $this->lang->line($module_name . '_id'));
        $this->setMetaTitlePattern('{date}');

        $this->setMetaDescriptionPattern('{address}', array($this, '_fb_format_meta_description'));
        $this->setOrderable(FALSE);

        $this->datagrid->setRowCssClassFormatingFunction(function ($line) {
            if ($line->is_incoming == 1) {
                return DataGrid::ROW_COLOR_BLUE;
            } else {
                return DataGrid::ROW_COLOR_RED;
            }
        });


        $this->datagrid->setItemsPerPage(300);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_from') . ')', 'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_GREATER_OR_EQUAL);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_to') . ')', 'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_LESS_OR_EQUAL);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date_sent') . ' (' . $this->lang->line('crud_label_from') . ')', 'date_sent', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_GREATER_OR_EQUAL);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date_sent') . ' (' . $this->lang->line('crud_label_to') . ')', 'date_sent', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_LESS_OR_EQUAL);


        // If not set, then DefaultFormRenderer is used
        // You can even use your own form templates, see views/templates
        $this->formbuilder->setRenderer(new FloatingFormRenderer());
        $this->formbuilder->setApplyButtonEnabled(TRUE);


        // Formbuilder callbacks
        $callbacks = array(
            '_fb_callback_on_save' => FormBuilder::CALLBACK_ON_SAVE,
        );
        // Assigning every single callback
        foreach ($callbacks as $callback_method_name => $callback_type) {
            // Attaching only when are callable
            if (is_callable(array($this, $callback_method_name))) {
                $this->formbuilder->setCallback(array($this, $callback_method_name), $callback_type);
            }
        }


        $definition = array(
            'address' => array(
                'filter_type' => DataGrid::FILTER_BASIC,
                'show_in_grid' => FALSE,
                'show_in_form' => TRUE,
                'input_type' => FormBuilder::TEXTFIELD,
                'validation_rules' => 'max_length[13]',
            ),
            'date' => array(
                'show_in_grid' => TRUE,
                'show_in_form' => FALSE,
                'input_type' => FormBuilder::TIMESTAMP,
                'validation_rules' => '',
                'input_default_value' => date('Y-m-d H:i:s'),
            ),
            'date_sent' => array(
                'show_in_grid' => TRUE,
                'show_in_form' => FALSE,
                'input_type' => FormBuilder::TIMESTAMP,
                'validation_rules' => '',
                'input_default_value' => date('Y-m-d H:i:s'),
            ),
            'body' => array(
                'filter_type' => DataGrid::FILTER_BASIC,
                'show_in_grid' => TRUE,
                'show_in_form' => TRUE,
                'input_type' => FormBuilder::TEXTAREA,
                'validation_rules' => 'max_length[480]',
            ),
            'is_incoming' => array(
                'filter_type' => DataGrid::FILTER_SELECT,
                'show_in_grid' => TRUE,
                'show_in_form' => FALSE,
                'input_type' => FormBuilder::CHECKBOX,
                'validation_rules' => 'numeric',
                'values' => array(
                    0 => $this->lang->line('global_dialog_no'),
                    1 => $this->lang->line('global_dialog_yes')
                ),
                'filter_values' => array(
                    0 => $this->lang->line('global_dialog_no'),
                    1 => $this->lang->line('global_dialog_yes')
                ),
            ),

        );


        // Getting translations and setting input groups
        foreach ($definition as $field => &$def) {
            $key = isset($def['field']) ? $def['field'] : $field;

            // Getting label
            if (!isset($def['label'])) {
                $def['label'] = $this->lang->line($module_name . '_' . $key);
            }

            // Getting description
            if (!isset($def['description'])) {
                $description = $this->lang->line($module_name . '_' . $key . '_description', FALSE);
                if ($description !== FALSE) {
                    $def['description'] = $description;
                }
            }

            // Setting default input group
            if (!isset($def['input_group']) || !$def['input_group']) {
                $def['input_group'] = 'default';
            }
        }

        // Here we go!
        $this->setDefinition($definition);
        $this->formbuilder->setSubmitLabel($this->lang->line('sms_api_submit_label'));
    }

    /**
     * Description format callback
     *
     * @param mixed $content Value of the element
     * @param object $line Object representing database row
     * @return string resulting text/html
     */
    public function _fb_format_meta_description($content, $line)
    {
        $this->load->helper('text');
        $content = strip_tags($content);
        return word_limiter($content, 10, '...');
    }


    /**
     * Must overwrite the save procedure and return true or false
     * @param array $data_array associative array made of filtered POST variables
     * @return bool
     */
    public function _fb_callback_on_save(&$data_array)
    {
        if ($this->formbuilder->getId()) {
            throw new UnexpectedValueException('Item modification is not supported');
        }


        return $this->Sms_api_model->sendMessage($data_array['address'], $data_array['body']);
    }
}