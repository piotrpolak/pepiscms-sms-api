<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * sms_api descriptor
 */
class Sms_apiDescriptor extends ModuleDescriptor
{

    /**
     * Cached variable
     *
     * @var String
     */
    private $module_name;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->module_name = strtolower(str_replace('Descriptor', '', __CLASS__));
        get_instance()->load->moduleLanguage($this->module_name);
    }

    /**
     * @inheritdoc
     */
    public function getName($language)
    {
        get_instance()->load->moduleLanguage($this->module_name);
        return get_instance()->lang->line($this->module_name . '_module_name');
    }

    /**
     * @inheritdoc
     */
    public function getDescription($language)
    {
        get_instance()->load->moduleLanguage($this->module_name);
        $description_label = $this->module_name . '_module_description';
        $description = get_instance()->lang->line($this->module_name . '_module_description');
        if ($description == $description_label) {
            return '';
        }

        return $description;
    }

    /**
     * @inheritdoc
     */
    public function onInstall()
    {
        return TRUE;
    }

    /**
     * @inheritdoc
     */
    public function onUninstall()
    {
        return TRUE;
    }

    /**
     * @inheritdoc
     */
    public function getAdminSubmenuElements($language)
    {
        return array(
            array(
                'controller' => $this->module_name,
                'method' => 'edit',
                'label' => get_instance()->lang->line($this->module_name . '_add'),
                'description' => '',
                'icon_url' => module_resources_url($this->module_name) . 'send_16.png'
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getConfigVariables()
    {
        return CrudDefinitionBuilder::create()
            ->withField('sms_api_feed_url')
                ->withInputDefaultValue('http://127.0.0.1/modules/sms_api/resources/sample/api/SmsInbox.json')
            ->end()
            ->withField('sms_api_send_url')
                ->withInputDefaultValue('http://127.0.0.1/modules/sms_api/resources/sample/api/SmsSend.json')
            ->end()
                ->withField('sms_api_max_feed_results')
            ->withInputDefaultValue(300)
                ->addValidationRule('required')
                ->addValidationRule('numeric')
            ->end()
                ->withField('sms_api_cache_ttl')
                ->withInputDefaultValue(10)
                ->addValidationRule('required')
                ->addValidationRule('numeric')
            ->end()
            ->withField('sms_api_blur_message_in_datagrid')
                ->withInputType(FormBuilder::CHECKBOX)
                ->withNoValidationRules()
            ->end()
            ->withImplicitTranslations($this->module_name, get_instance()->lang)
            ->build();
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayedInMenu()
    {
        return TRUE;
    }
}