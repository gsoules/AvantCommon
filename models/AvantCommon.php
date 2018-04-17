<?php
class AvantCommon
{
    public static function saveConfiguration()
    {
        $errorMessage = '';

        $identifierElementName = $_POST['avantcommon_identifier'];
        $identifierAliasElementName = $_POST['avantcommon_identifier_alias'];

        $elementId = ItemMetadata::getElementIdForElementName($identifierElementName);
        if ($elementId == 0)
        {
            $errorMessage = __('"%s" is not an element (Element names are case-sensitive)', $identifierElementName);
        }
        else
        {
            set_option('avantcommon_identifier', $elementId);
        }

        if (!empty($errorMessage))
        {
            throw new Omeka_Validate_Exception($errorMessage);
        }

        $elementId = ItemMetadata::getElementIdForElementName($identifierAliasElementName);
        if (!empty($identifierAliasElementName) && $elementId == 0)
        {
            $errorMessage = __('"%s" is not an element', $identifierAliasElementName);
        }
        else
        {
            set_option('avantcommon_identifier_alias', $elementId);
        }

        if (!empty($errorMessage))
        {
            throw new Omeka_Validate_Exception($errorMessage);
        }

        set_option('avantcommon_identifier_prefix', $_POST['avantcommon_identifier_prefix']);
    }
}