<?php

class AvantCommonPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'config',
        'config_form',
        'initialize',
        'install',
        'public_head'
    );

    protected $_filters = array(
        'display_elements'
    );

    public function filterDisplayElements($elementsBySet)
    {
        // Omeka calls this Display Elements filter to give plugins an opportunity to remove elements from the set
        // of elements that appear on the Show pages. This code hides unused elements from both the admin and public
        // Show pages. It also hides private elements from the public Show page.

        $isAdminTheme = is_admin_theme();
        $privateElementsData = CommonConfig::getOptionDataForPrivateElements();
        $unusedElementsData = CommonConfig::getOptionDataForUnusedElements();

        foreach ($elementsBySet as $elementSetName => $elementSet)
        {
            foreach ($elementSet as $elementName => $element)
            {
                $elementId = $element->id;
                $hide = array_key_exists($elementId, $unusedElementsData);
                $hide = $hide || (!$isAdminTheme && array_key_exists($elementId, $privateElementsData));
                if ($hide)
                {
                    unset($elementsBySet[$elementSetName][$elementName]);
                }
            }
        }

        return $elementsBySet;
    }

    public function filterElementSetForm($elements, $args)
    {
        // Omeka calls the Element Set Form Filter to give plugins an opportunity to remove elements from the set
        // of elements that appear on the admin Edit Item page. It is called once for Dublin Core elements
        // and again for Item Type Metadata elements.

        $unusedElements = CommonConfig::getOptionDataForUnusedElements();
        foreach ($elements as $key => $element)
        {
            $elementId = $element->id;
            if (array_key_exists($elementId, $unusedElements))
            {
                unset($elements[$key]);
            }
        }

        return $elements;
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantcommon-admin');
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

    public function hookInitialize()
    {
        // Initialize the filters that will prevent unused elements from appearing on the admin Edit Item form.
        add_filter(array('ElementSetForm', 'Item', 'Dublin Core'), array($this, 'filterElementSetForm'));
        add_filter(array('ElementSetForm', 'Item', 'Item Type Metadata'), array($this, 'filterElementSetForm'));
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
        //if (plugin_is_active('AvantSearch') || plugin_is_active('AvantRelationships'))
        {
            queue_css_file('magnific-popup');
            queue_js_file('jquery.magnific-popup.min');
        }

        queue_css_file('avantcommon');
    }
}
