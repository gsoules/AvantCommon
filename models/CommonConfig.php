<?php
class CommonConfig
{
    const OPTION_IDENTIFIER = 'avantcommon_identifier';
    const OPTION_IDENTIFIER_ALIAS = 'avantcommon_identifier_alias';
    const OPTION_IDENTIFIER_PREFIX = 'avantcommon_identifier_prefix';
    const OPTION_YEAR_START = 'avantcommon_year_start';
    const OPTION_YEAR_END = 'avantcommon_year_end';

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

    public static function getOptionDataForYearEnd()
    {
        return get_option(self::OPTION_YEAR_END);
    }

    public static function getOptionDataForYearStart()
    {
        return get_option(self::OPTION_YEAR_START);
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
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Identifier Alias') . ': ' . __('"%s" is not an element.)', $elementName));
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
        self::saveOptionDataForYearStartEnd();
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

    public static function saveOptionDataForYearStartEnd()
    {
        $startYearElementId = 0;
        $endYearElementId = 0;

        $startYear = trim($_POST[self::OPTION_YEAR_START]);
        if (!empty($startYear))
        {
            $startYearElementId = ItemMetadata::getElementIdForElementName($startYear);
            if ($startYearElementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Start Year') . ': ' . __('\'%s\' is not an element.', $startYear));
            }
        }

        $endYear = trim($_POST[self::OPTION_YEAR_END]);
        if (!empty($endYear))
        {
            $endYearElementId = ItemMetadata::getElementIdForElementName($endYear);
            if ($endYearElementId == 0)
            {
                throw new Omeka_Validate_Exception(__('End Year') . ': ' . __('\'%s\' is not an element.', $endYear));
            }
        }

        $count = $startYearElementId == 0 ? 0 : 1;
        $count += $endYearElementId == 0 ? 0 : 1;
        if (!($count == 0 || $count == 2))
        {
            throw new Omeka_Validate_Exception(__('Both the Start Year and End Year must be specified or both must be blank.'));
        }

        if ($count == 2 && $startYearElementId == $endYearElementId)
        {
            throw new Omeka_Validate_Exception(__('Start Yead and End Year cannot be the same element'));
        }

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