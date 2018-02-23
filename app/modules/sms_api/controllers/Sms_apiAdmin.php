<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property FormBuilder $formbuilder
 * @property DataGrid $datagrid
 * @property PEPISCMS_Loader $load
 * @property PEPISCMS_Config $config
 * @property PEPISCMS_Lang $lang
 * @property PEPISCMS_Input $input
 *
 * @property Sms_api_model $Sms_api_model
 */
class Sms_apiAdmin extends AdminCRUDController
{
    public function __construct()
    {
        parent::__construct();

        $module_name = $this->getModuleName();

        $this->load->moduleLanguage($module_name, $module_name);
        $this->load->moduleConfig($module_name);
        $this->load->moduleModel($module_name, 'Sms_api_model');

        $this->Sms_api_model->setFeedUrl($this->config->item('sms_api_feed_url'))
            ->setSendUrl($this->config->item('sms_api_send_url'))
            ->setMaxFeedResults($this->config->item('sms_api_max_feed_results'))
            ->setCacheTtl($this->config->item('sms_api_cache_ttl'));

        $that = $this;

        $this->setFeedObject($this->Sms_api_model)
            ->setPageTitle($this->lang->line($module_name . '_module_name'))
            ->setAddNewItemLabel($this->lang->line($module_name . '_add'))
            ->setTooltipTextForIndex($this->lang->line($module_name . '_index_tip'))
            ->setTooltipTextForEdit($this->lang->line($module_name . '_edit_tip'))
            ->setDeletable(FALSE)
            ->setAddable(TRUE)
            ->setEditable(FALSE)
            ->setPreviewable(TRUE)
            ->setPopupEnabled(FALSE)
            ->setOrderable(FALSE)
            ->setExportable(TRUE, function ($resulting_line) {
                unset($resulting_line['id']); // Do not export id field
                return $resulting_line;
            })
            ->setMetaOrderField('id', $this->lang->line($module_name . '_id'))
            ->setMetaTitlePattern('{date}')
            ->setMetaDescriptionPattern('{address}', function ($content, $line) use ($that) {
                $that->load->helper('text');
                $content = strip_tags($content);
                return word_limiter($content, 10, '...');
            })
            ->setOrderable(FALSE);


        $this->datagrid->setItemsPerPage(30)
            ->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_from') . ')',
                'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_GREATER_OR_EQUAL)
            ->addFilter($this->lang->line($module_name . '_date') . ' (' . $this->lang->line('crud_label_to') . ')',
                'date', DataGrid::FILTER_DATE, FALSE, DataGrid::FILTER_CONDITION_LESS_OR_EQUAL)
            ->setRowCssClassFormattingFunction(function ($line) {
                if ($line->is_incoming == 1) {
                    return DataGrid::ROW_COLOR_BLUE;
                } else {
                    return DataGrid::ROW_COLOR_ORANGE;
                }
            });


        $this->formbuilder->setRenderer(new FloatingFormRenderer())
            ->setApplyButtonEnabled(TRUE)
            ->setSubmitLabel($this->lang->line($module_name . '_submit_label'))
            ->setCallback(function (&$data_array) use ($that) {
                if ($that->formbuilder->getId()) {
                    throw new UnexpectedValueException('Item modification is not supported');
                }

                if ($that->Sms_api_model->sendMessage($data_array['address'], $data_array['body'])) {
                    Logger::info('Message sent successfully. address: ' . $data_array['address'] .
                        ' message:' . $data_array['body'], 'sms');
                    return TRUE;
                }
                Logger::error('Unable to send message. address: ' . $data_array['address'] .
                    ' message:' . $data_array['body'], 'sms');
                return FALSE;
            }, FormBuilder::CALLBACK_ON_SAVE);


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
                ->withGridFormattingCallback(function($value, $line) use($that) {
                    if($that->config->item('sms_api_send_url')) {
                        return preg_replace('/[a-zA-Z]/', '.', $value);
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
    }
}