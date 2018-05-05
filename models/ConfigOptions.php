<?php
class ConfigOptions
{
    protected static function configurationErrorsDetected()
    {
        // When a configuration occurs, the Configure Plugin page posts back to itself to display the error
        // after the user presses the Save button.
        $isConfigSave = isset($_POST['install_plugin']);
        return $isConfigSave;
    }

    public static function emitOptionNotSupported($pluginName, $hash)
    {
        echo "<p class='explanation learn-more'>" . __('Option not available for this installation. ');
        echo "<a class='avantsearch-help' href='https://github.com/gsoules/$pluginName#$hash' target='_blank'>" . __('Learn more.') . "</a>";
        echo "</p>";
    }

    protected static function errorIf($condition, $optionLabel, $message)
    {
        if ($condition)
        {
            $exceptionText = empty($optionLabel) ? $message : "$optionLabel: $message";
            throw new Omeka_Validate_Exception($exceptionText);
        }
    }

    protected static function errorIfNotElement($elementId, $optionLabel, $elementName)
    {
        self::errorIf($elementId == 0, $optionLabel, __("'%s' is not an element.", $elementName));
    }

    protected static function errorRowIf($condition, $optionLabel, $rowId, $message)
    {
        if ($condition)
        {
            throw new Omeka_Validate_Exception("$optionLabel ($rowId): $message");
        }
    }

    public static function getOptionDefinitionData($optionName)
    {
        $rawData = self::getRawData($optionName);
        $optionData = array();

        foreach ($rawData as $elementId => $data)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                // This element must have been deleted since the AvantElements configuration was last saved.
                continue;
            }
            $data['name'] = $elementName;
            $optionData[$elementId] = $data;
        }

        return $optionData;
    }

    protected static function getOptionListData($optionName)
    {
        $rawData = self::getRawData($optionName);
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

    protected static function getOptionListText($optionName)
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[$optionName];
        }
        else
        {
            $data = self::getOptionListData($optionName);
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

    protected static function getRawData($option)
    {
        $rawData = json_decode(get_option($option), true);
        if (empty($rawData))
        {
            $rawData = array();
        }
        return $rawData;
    }

    protected static function saveOptionListData($optionName, $optionLabel)
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
}