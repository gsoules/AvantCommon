<?php

define('CONFIG_LABEL_IDENTIFIER', __('Identifier Element'));
define('CONFIG_LABEL_IDENTIFIER_ALIAS', __('Identifier Alias'));
define('CONFIG_LABEL_IDENTIFIER_PREFIX', __('Identifier Prefix'));
define('CONFIG_LABEL_YEAR_START', __('Start Year'));
define('CONFIG_LABEL_YEAR_END', __('End Year'));

class CommonConfig extends ConfigOptions
{
    const OPTION_IDENTIFIER = 'avantcommon_identifier';
    const OPTION_IDENTIFIER_ALIAS = 'avantcommon_identifier_alias';
    const OPTION_IDENTIFIER_PREFIX = 'avantcommon_identifier_prefix';
    const OPTION_YEAR_START = 'avantcommon_year_start';
    const OPTION_YEAR_END = 'avantcommon_year_end';

    public static function getOptionDataForIdentifier()
    {
        return get_option(self::OPTION_IDENTIFIER);
    }

    public static function getOptionDataForIdentifierAlias()
    {
        return get_option(self::OPTION_IDENTIFIER_ALIAS);
    }

    public static function getOptionDataForYearEnd()
    {
        return get_option(self::OPTION_YEAR_END);
    }

    public static function getOptionDataForYearStart()
    {
        return get_option(self::OPTION_YEAR_START);
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

    public static function getOptionTextForYearEnd()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_YEAR_END];
        }
        else
        {
            $text = ItemMetadata::getElementNameFromId(get_option(self::OPTION_YEAR_END));
        }
        return $text;
    }

    public static function getOptionTextForYearStart()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_YEAR_START];
        }
        else
        {
            $text = ItemMetadata::getElementNameFromId(get_option(self::OPTION_YEAR_START));
        }
        return $text;
    }

    public static function saveOptionDataForIdentifier()
    {
        $identifierElementName = $_POST[self::OPTION_IDENTIFIER];
        $elementId = ItemMetadata::getElementIdForElementName($identifierElementName);
        if ($elementId == 0)
        {
            throw new Omeka_Validate_Exception(__('Identifier Element') . ': ' . __('"%s" is not an element.', $identifierElementName));
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

    public static function saveConfiguration()
    {
        self::saveOptionDataForIdentifier();
        self::saveOptionDataForIdentifierAlias();
        self::saveOptionDataForIdentifierPrefix();
        self::saveOptionDataForYearStartEnd();
    }

    public static function saveOptionDataForYearStartEnd()
    {
        $startYearElementId = 0;
        $endYearElementId = 0;

        $startYear = trim($_POST[self::OPTION_YEAR_START]);
        if (!empty($startYear))
        {
            $startYearElementId = ItemMetadata::getElementIdForElementName($startYear);
            self::errorIf($startYearElementId == 0, CONFIG_LABEL_YEAR_START, __("'%s' is not an element.", $startYear));
        }

        $endYear = trim($_POST[self::OPTION_YEAR_END]);
        if (!empty($endYear))
        {
            $endYearElementId = ItemMetadata::getElementIdForElementName($endYear);
            self::errorIf($endYearElementId == 0, CONFIG_LABEL_YEAR_END, __("'%s' is not an element.", $endYear));
        }

        $count = $startYearElementId == 0 ? 0 : 1;
        $count += $endYearElementId == 0 ? 0 : 1;
        self::errorIf(!($count == 0 || $count == 2), null, _('Both the Start Year and End Year must be specified or both must be blank.'));
        self::errorIf($count == 2 && $startYearElementId == $endYearElementId, null, _('Start Yead and End Year cannot be the same element.'));

        set_option(self::OPTION_YEAR_START, $startYearElementId);
        set_option(self::OPTION_YEAR_END, $endYearElementId);
    }

    public static function setDefaultOptionValues()
    {
        $identifierElementId = ItemMetadata::getElementIdForElementName('Identifier');
        set_option(self::OPTION_IDENTIFIER, $identifierElementId);

        set_option(self::OPTION_IDENTIFIER_ALIAS, 0);
        set_option(self::OPTION_IDENTIFIER_PREFIX, __('Item'));
        set_option(self::OPTION_YEAR_START, 0);
        set_option(self::OPTION_YEAR_END, 0);
    }
}