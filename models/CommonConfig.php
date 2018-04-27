<?php
class CommonConfig
{
    const OPTION_IDENTIFIER = 'avantcommon_identifier';
    const OPTION_IDENTIFIER_ALIAS = 'avantcommon_identifier_alias';
    const OPTION_IDENTIFIER_PREFIX = 'avantcommon_identifier_prefix';
    const OPTION_START_END_YEARS = 'avantcommon_start_end_years';

    protected static function configurationErrorsDetected()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    protected static function getOptionData($optionName)
    {
        $rawData = json_decode(get_option($optionName), true);
        if (empty($rawData))
        {
            $rawData = array();
        }

        $data = array();

        foreach ($rawData as $elementId)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                // This element must have been deleted since the configuration was last saved.
                continue;
            }
            $data[$elementId] = $elementName;
        }

        return $data;
    }

    public static function getOptionDataForIdentifier()
    {
        return get_option(self::OPTION_IDENTIFIER);
    }

    public static function getOptionDataForIdentifierAlias()
    {
        return get_option(self::OPTION_IDENTIFIER_ALIAS);
    }

    public static function getOptionDataForStartEndYears()
    {
        return self::getOptionData(self::OPTION_START_END_YEARS);
    }

    protected static function getOptionText($optionName)
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[$optionName];
        }
        else
        {
            $data = self::getOptionData($optionName);
            $text = '';
            foreach ($data as $elementName)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $text .= $elementName;
            }
        }
        return $text;
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

    public static function getOptionTextForStartEndYears()
    {
        return self::getOptionText(self::OPTION_START_END_YEARS);
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
        $identifierAliasElementName = trim($_POST[self::OPTION_IDENTIFIER_ALIAS]);
        if (!empty($identifierAliasElementName))
        {
            $elementId = ItemMetadata::getElementIdForElementName($identifierAliasElementName);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Identifier Alias') . ': ' . __('"%s" is not an element.)', $identifierAliasElementName));
            }
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
        self::saveOptionDataForStartEndYears();
    }

    protected static function saveOptionData($optionName, $optionLabel)
    {
        $elements = array();
        $names = array_map('trim', explode(PHP_EOL, $_POST[$optionName]));
        foreach ($names as $name)
        {
            if (empty($name))
                continue;
            $elementId = ItemMetadata::getElementIdForElementName($name);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception($optionLabel . ': ' . __('\'%s\' is not an element.', $name));
            }
            $elements[] = $elementId;
        }

        set_option($optionName, json_encode($elements));
    }

    public static function saveOptionDataForStartEndYears()
    {
        $elements = array();
        $names = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_START_END_YEARS]));
        foreach ($names as $name)
        {
            if (empty($name))
                continue;
            $elementId = ItemMetadata::getElementIdForElementName($name);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Start/End Years') . ': ' . __('\'%s\' is not an element.', $name));
            }
            $elements[] = $elementId;
        }

        $count = count($elements);
        if (!($count == 0 || $count == 2))
        {
            throw new Omeka_Validate_Exception(__('Start/End Years') . ': ' . __('Exactly two elements are required to specify Start Year and End Year.', $name));
        }

        if ($count == 2 && $elements[0] == $elements[1])
        {
            throw new Omeka_Validate_Exception(__('Start/End Years') . ': ' . __('The start and end year cannot be the same element', $name));
        }

        set_option(self::OPTION_START_END_YEARS, json_encode($elements));
    }

    public static function setDefaultOptionValues()
    {
        $identifierElementId = ItemMetadata::getElementIdForElementName('Identifier');
        set_option(self::OPTION_IDENTIFIER, $identifierElementId);

        set_option(self::OPTION_IDENTIFIER_ALIAS, 0);
        set_option(self::OPTION_IDENTIFIER_PREFIX, __('Item'));
        set_option(self::OPTION_START_END_YEARS, '[]');
    }
}