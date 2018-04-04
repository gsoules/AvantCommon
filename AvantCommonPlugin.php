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
        set_option('common_identifier', $_POST['common_identifier']);
        set_option('common_identifier_alias', $_POST['common_identifier_alias']);
        set_option('common_identifier_prefix', $_POST['common_identifier_prefix']);
        set_option('common_title', $_POST['common_title']);
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
        queue_css_file('avant-common');

        if (plugin_is_active('AvantCustom') || plugin_is_active('AvantRelationships'))
        {
            queue_css_file('magnific-popup');
            queue_js_file('jquery.magnific-popup.min');
        }
    }
}
