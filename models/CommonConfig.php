<?php

define('CONFIG_LABEL_IDENTIFIER', __('Identifier Element'));
define('CONFIG_LABEL_IDENTIFIER_ALIAS', __('Identifier Alias'));
define('CONFIG_LABEL_IDENTIFIER_PREFIX', __('Identifier Prefix'));
define('CONFIG_LABEL_LIGHTBOX', __('Enable Lightbox'));
define('CONFIG_LABEL_PRIVATE_ELEMENTS', __('Private Elements'));
define('CONFIG_LABEL_REQUEST_IMAGE_URL', __('Request Image URL'));
define('CONFIG_LABEL_UNUSED_ELEMENTS', __('Unused Elements'));

class CommonConfig extends ConfigOptions
{
    const OPTION_IDENTIFIER = 'avantcommon_identifier';
    const OPTION_IDENTIFIER_ALIAS = 'avantcommon_identifier_alias';
    const OPTION_IDENTIFIER_PREFIX = 'avantcommon_identifier_prefix';
    const OPTION_LIGHTBOX = 'avantcommon_lightbox';
    const OPTION_PRIVATE_ELEMENTS = 'avantcommon_private_elements';
    const OPTION_REQUEST_IMAGE_URL = 'avantcommon_request_image_url';
    const OPTION_UNUSED_ELEMENTS = 'avantcommon_unsused_elements';

    public static function getOptionDataForIdentifier()
    {
        return get_option(self::OPTION_IDENTIFIER);
    }

    public static function getOptionDataForIdentifierAlias()
    {
        return get_option(self::OPTION_IDENTIFIER_ALIAS);
    }

    public static function getOptionDataForPrivateElements()
    {
        return self::getOptionListData(self::OPTION_PRIVATE_ELEMENTS);
    }

    public static function getOptionDataForRequestImageUrl()
    {
        return get_option(self::OPTION_REQUEST_IMAGE_URL);
    }

    public static function getOptionDataForUnusedElements()
    {
        return self::getOptionListData(self::OPTION_UNUSED_ELEMENTS);
    }

    public static function getOptionTextForIdentifier()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_IDENTIFIER];
        }
        else
        {
            $text = ItemMetadata::getElementNameFromId(get_option(self::OPTION_IDENTIFIER));
        }
        return $text;
    }

    public static function getOptionTextForIdentifierAlias()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_IDENTIFIER_ALIAS];
        }
        else
        {
            $text = ItemMetadata::getElementNameFromId(get_option(self::OPTION_IDENTIFIER_ALIAS));
        }
        return $text;
    }

    public static function getOptionTextForIdentifierPrefix()
    {
        return get_option(self::OPTION_IDENTIFIER_PREFIX);
    }

    public static function getOptionTextForPrivateElements()
    {
        return self::getOptionListText(self::OPTION_PRIVATE_ELEMENTS);
    }

    public static function getOptionTextForRequestImageUrl()
    {
        return get_option(self::OPTION_REQUEST_IMAGE_URL);
    }

    public static function getOptionTextForUnusedElements()
    {
        return self::getOptionListText(self::OPTION_UNUSED_ELEMENTS);
    }

    public static function saveConfiguration()
    {
        self::saveOptionDataForIdentifier();
        self::saveOptionDataForIdentifierAlias();
        self::saveOptionDataForIdentifierPrefix();
        self::saveOptionDataForPrivateElements();
        self::saveOptionDataForRequestImageUrl();
        self::saveOptionDataForUnusedElements();

        set_option(self::OPTION_LIGHTBOX, intval($_POST[self::OPTION_LIGHTBOX]));
    }

    public static function saveOptionDataForIdentifier()
    {
        $identifierElementName = $_POST[self::OPTION_IDENTIFIER];
        $elementId = ItemMetadata::getElementIdForElementName($identifierElementName);
        if ($elementId == 0)
        {
            throw new Omeka_Validate_Exception(CONFIG_LABEL_IDENTIFIER . ': ' . __('"%s" is not an element.', $identifierElementName));
        }
        set_option(self::OPTION_IDENTIFIER, $elementId);
    }

    public static function saveOptionDataForIdentifierAlias()
    {
        $elementName = trim($_POST[self::OPTION_IDENTIFIER_ALIAS]);
        $elementId = 0;

        if (!empty($elementName))
        {
            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIf($elementId == 0, CONFIG_LABEL_IDENTIFIER_ALIAS, __("'%s' is not an element.", $elementName));
        }
        set_option(self::OPTION_IDENTIFIER_ALIAS, $elementId);
    }

    public static function saveOptionDataForIdentifierPrefix()
    {
        set_option(self::OPTION_IDENTIFIER_PREFIX, $_POST[self::OPTION_IDENTIFIER_PREFIX]);
    }

    public static function saveOptionDataForPrivateElements()
    {
        self::saveOptionListData(self::OPTION_PRIVATE_ELEMENTS, CONFIG_LABEL_PRIVATE_ELEMENTS);
    }

    public static function saveOptionDataForRequestImageUrl()
    {
        set_option(self::OPTION_REQUEST_IMAGE_URL, $_POST[self::OPTION_REQUEST_IMAGE_URL]);
    }

    public static function saveOptionDataForUnusedElements()
    {
        self::saveOptionListData(self::OPTION_UNUSED_ELEMENTS, CONFIG_LABEL_UNUSED_ELEMENTS);
    }

    public static function setDefaultOptionValues()
    {
        $identifierElementId = ItemMetadata::getElementIdForElementName('Identifier');
        set_option(self::OPTION_IDENTIFIER, $identifierElementId);

        set_option(self::OPTION_IDENTIFIER_ALIAS, 0);
        set_option(self::OPTION_IDENTIFIER_PREFIX, __('Item '));
    }
}