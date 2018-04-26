<?php
class ConfigurationOptions
{
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
}