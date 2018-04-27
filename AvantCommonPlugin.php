<?php

class AvantCommonPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'config',
        'config_form',
        'install',
        'public_head'
    );

    protected $_filters = array(
    );

    public function hookAdminHead($args)
    {
        $this->head();
    }

    public function hookConfig()
    {
        CommonConfig::saveConfiguration();
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookInstall()
    {
        CommonConfig::setDefaultOptionValues();
    }

    public function hookPublicHead($args)
    {
        $this->head();
    }

    protected function head()
    {
        if (plugin_is_active('AvantSearch') || plugin_is_active('AvantRelationships'))
        {
            queue_css_file('magnific-popup');
            queue_js_file('jquery.magnific-popup.min');
        }
        queue_css_file('avantcommon');
    }
}
