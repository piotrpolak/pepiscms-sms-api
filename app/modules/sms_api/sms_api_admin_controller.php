<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * sms_api admin controller
 */
class Sms_apiAdmin extends AdminCRUDController
{
    /**
     * Default constructor containing all necessary definitions
     */
    public function __construct()
    {
        parent::__construct();

        $module_name = $this->getModuleName();
        $model_name = $this->getModelName();

        $this->load->moduleLanguage($module_name, $module_name);
        $this->load->moduleConfig($module_name);
        $this->load->moduleModel($module_name, $model_name);

        $this->$model_name->setFeedUrl($this->config->item('sms_api_feed_url'));
        $this->$model_name->setSendUrl($this->config->item('sms_api_send_url'));
        $this->$model_name->setMaxFeedResults($this->config->item('sms_api_max_feed_results'));
        $this->$model_name->setCacheTtl($this->config->item('sms_api_cache_ttl'));

        $this->setFeedObject($this->$model_name);

        $this->setPageTitle($this->lang->line($module_name . '_module_name'));
        $this->setAddNewItemLabel($this->lang->line($module_name . '_add'));
        $this->setTooltipTextForIndex($this->lang->line($module_name . '_index_tip'));
        $this->setTooltipTextForEdit($this->lang->line($module_name . '_edit_tip'));

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

        $this->datagrid->setRowCssClassFormattingFunction(function ($line) {
            if ($line->is_incoming == 1) {
                return DataGrid::ROW_COLOR_BLUE;
            } else {
                return DataGrid::ROW_COLOR_ORANGE;
            }
        });


        $this->datagrid->setItemsPerPage(30);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_from') . ')', 'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_GREATER_OR_EQUAL);
        $this->datagrid->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_to') . ')', 'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_LESS_OR_EQUAL);

        $this->formbuilder->setRenderer(new FloatingFormRenderer());
        $this->formbuilder->setApplyButtonEnabled(TRUE);


        $callbacks = array(
            '_fb_callback_on_save' => FormBuilder::CALLBACK_ON_SAVE,
        );
        foreach ($callbacks as $callback_method_name => $callback_type) {
            if (is_callable(array($this, $callback_method_name))) {
                $this->formbuilder->setCallback(array($this, $callback_method_name), $callback_type);
            }
        }

        $is_incoming_values = array(
            0 => $this->lang->line('global_dialog_no'),
            1 => $this->lang->line('global_dialog_yes')
        );

        $definition = CrudDefinitionBuilder::create()
            ->withField('address')
                ->withFilterType(DataGrid::FILTER_BASIC)
                ->withShowInGrid(FALSE)
                ->withShowInForm(TRUE)
                ->withInputType(FormBuilder::TEXTFIELD)
                ->addValidationRule('required')
                ->addValidationRule('valid_phone_number')
                ->addValidationRule('max_length[13]')
            ->end()
            ->withField('date')
                ->withShowInGrid(TRUE)
                ->withShowInForm(FALSE)
                ->withInputType(FormBuilder::TEXTFIELD)
            ->end()
                ->withField('date_sent')
                ->withShowInGrid(TRUE)
                ->withShowInForm(FALSE)
                ->withInputType(FormBuilder::TEXTFIELD)
            ->end()
            ->withField('body')
                ->withFilterType(DataGrid::FILTER_BASIC)
                ->withShowInGrid(TRUE)
                ->withShowInForm(TRUE)
                ->withInputType(FormBuilder::TEXTAREA)
                ->addValidationRule('required')
                ->addValidationRule('max_length[480]')
                ->withGridFormattingCallback(function($value, $line) {
                    if($this->config->item('sms_api_send_url')) {
                        return preg_replace("/[a-zA-Z]/", '.', $value);
                    }

                    return $value;
                })
            ->end()
            ->withField('is_incoming')
                ->withFilterType(DataGrid::FILTER_SELECT)
                ->withValues($is_incoming_values)
                ->withFilterValues($is_incoming_values)
                ->withShowInGrid(TRUE)
                ->withShowInForm(FALSE)
                ->withInputType(FormBuilder::TEXTAREA)
                ->withNoValidationRules()
            ->end()
            ->withImplicitTranslations($module_name, $this->lang)
            ->build();

        $this->setDefinition($definition);
        $this->formbuilder->setSubmitLabel($this->lang->line($module_name . '_submit_label'));
    }

    /**
     * Description formatting callback.
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
     * Must overwrite the save procedure and return true or false.
     *
     * @param array $data_array associative array made of filtered POST variables
     * @return bool
     */
    public function _fb_callback_on_save(&$data_array)
    {
        if ($this->formbuilder->getId()) {
            throw new UnexpectedValueException('Item modification is not supported');
        }

        if ($this->Sms_api_model->sendMessage($data_array['address'], $data_array['body'])) {
            Logger::info('Message sent successfully. address: ' . $data_array['address'] . ' message:' . $data_array['body'], 'sms');
            return TRUE;
        }
        Logger::error('Unable to send message. address: ' . $data_array['address'] . ' message:' . $data_array['body'], 'sms');
        return FALSE;
    }
}