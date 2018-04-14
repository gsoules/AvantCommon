<?php

class AvantCommonPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'config',
        'config_form',
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
        set_option('avantcommon_identifier', $_POST['avantcommon_identifier']);
        set_option('avantcommon_identifier_alias', $_POST['avantcommon_identifier_alias']);
        set_option('avantcommon_identifier_prefix', $_POST['avantcommon_identifier_prefix']);
        set_option('avantcommon_title', $_POST['avantcommon_title']);
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
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
