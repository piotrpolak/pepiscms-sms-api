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
     * Default constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->module_name = strtolower(str_replace('Descriptor', '', __CLASS__));
        get_instance()->load->moduleLanguage($this->module_name);
    }

    /**
     * Returns module name
     */
    public function getName($language)
    {
        get_instance()->load->moduleLanguage($this->module_name);
        return get_instance()->lang->line($this->module_name . '_module_name');
    }

    /**
     * Returns module description
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
     * Executed on installation
     */
    public function onInstall()
    {
        return TRUE;
    }

    /**
     * Executed on uninstall
     */
    public function onUninstall()
    {
        return TRUE;
    }

    /**
     * Returns the list of module submenu elements
     */
    public function getAdminSubmenuElements($language)
    {
        return array(
            array(
                'controller' => $this->module_name,
                'method' => 'edit',
                'label' => get_instance()->lang->line($this->module_name . '_add'),
                'description' => ''
            ),
        );
    }

    /**
     * Returns the list of module landing dashboard elements
     */
    public function getAdminDashboardElements($language)
    {
        return FALSE;
    }

    public function getConfigVariables()
    {
        return array(
            'sms_api_feed_url' => array(
                'label' => 'Feed URL',
                'input_default_value' => 'http://127.0.0.1/modules/sms_api/resources/sample/api/SmsInbox.json',
            ),
            'sms_api_send_url' => array(
                'label' => 'Feed URL',
                'input_default_value' => 'http://127.0.0.1/modules/sms_api/resources/sample/api/SmsSend.json',
            ),
        );
    }

}