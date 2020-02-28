<?php

class AvantCommonPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'config',
        'config_form',
        'initialize',
        'install',
        'public_footer',
        'public_head'
    );

    protected $_filters = array(
        'display_elements'
    );

    public function __call($filterName, $args)
    {
        // Handle filter requests for filterPrivateElement.
        $result = null;
        $item = $args[1]['record'];
        $text = $args[0];

        if (strpos($filterName, 'filterPrivateElement') === 0)
        {
            $result = $this->filterPrivateElement($text);
        }

        return $result;
    }

    public function filterDisplayElements($elementsBySet)
    {
        // Omeka calls this Display Elements filter to give plugins an opportunity to remove elements from the set
        // of elements that appear on the Show pages. This code hides unused elements from both the admin and public
        // Show pages. It also hides private elements from the public Show page.

        $hidePrivate = empty(current_user());
        $privateElementsData = CommonConfig::getOptionDataForPrivateElements();
        $unusedElementsData = CommonConfig::getOptionDataForUnusedElements();

        foreach ($elementsBySet as $elementSetName => $elementSet)
        {
            foreach ($elementSet as $elementName => $element)
            {
                $elementId = $element->id;
                $hideUnused = array_key_exists($elementId, $unusedElementsData);
                $hide = $hideUnused || ($hidePrivate && array_key_exists($elementId, $privateElementsData));
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

    public function filterPrivateElement($text)
    {
        if (empty(current_user()))
        {
            // No user is logged in. Don't filter private elements because they will be hidden by filterDisplayElements().
            return $text;
        }

        // Display this private element to a logged in user, but add a class to change the text styling to indicate private.
        $text = html_entity_decode($text);
        return "<span class='private-element'>$text</span>";
    }

    protected function head()
    {
        queue_css_file('magnific-popup');
        queue_js_file('jquery.magnific-popup.min');
        queue_js_file('js.cookie');
        queue_js_file('avantcommon-script');

        queue_css_file('avantcommon');
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

    public function hookPublicFooter($args)
    {
        if (get_option(CommonConfig::OPTION_LIGHTBOX))
        {
            $requestImageUrl = get_option(CommonConfig::OPTION_REQUEST_IMAGE_URL);

            echo get_view()->partial(
                'avantcommon-script.php',
                array(
                'itemLinkText' => __('View Item'),
                'requestImageText' => __('Request Image'),
                'requestImageUrl' => $requestImageUrl
                ));
        }
    }

    public function hookPublicHead($args)
    {
        $this->head();
    }
}
