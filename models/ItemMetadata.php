<?php

class ItemMetadata
{
    public static function getAllElementTextsForElementName($item, $elementName)
    {
        $elementSetName = self::getElementSetNameForElementName($elementName);
        $elementTexts = $item->getElementTexts($elementSetName, $elementName);
        $texts = array();
        foreach ($elementTexts as $elementText)
        {
            $texts[] = $elementText->getText();
        }
        return $texts;
    }

    public static function getElementIdForElementName($elementName)
    {
        $db = get_db();
        $elementTable = $db->getTable('Element');
        $element = $elementTable->findByElementSetNameAndElementName('Dublin Core', $elementName);
        if (empty($element))
            $element = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', $elementName);
        return empty($element) ? 0 : $element->id;
    }

    public static function getElementNameFromId($elementId)
    {
        $db = get_db();
        $element = $db->getTable('Element')->find($elementId);
        return isset($element) ? $element->name : '';
    }

    public static function getElementSetNameForElementName($elementName)
    {
        $db = get_db();
        $elementTable = $db->getTable('Element');

        $elementSetName = 'Dublin Core';
        $element = $elementTable->findByElementSetNameAndElementName($elementSetName, $elementName);
        if (empty($element))
        {
            $elementSetName = 'Item Type Metadata';
            $element = $elementTable->findByElementSetNameAndElementName($elementSetName, $elementName);
        }
        return empty($element) ? '' : $elementSetName;
    }

    public static function getElementTextsByValue($elementId, $value)
    {
        $results = array();

        if (!empty($value))
        {
            $db = get_db();
            $select = $db->select()
                ->from($db->ElementText)
                ->where('element_id = ?', $elementId)
                ->where('text = ?', $value)
                ->where('record_type = ?', 'Item');
            $results = $db->getTable('ElementText')->fetchObjects($select);
        }

        return $results;
    }

    public static function getElementTextForElementName($item, $elementName, $asHtml = true)
    {
        try
        {
            $elementSetName = self::getElementSetNameForElementName($elementName);
            $text = metadata($item, array($elementSetName, $elementName), array('no_filter' => true, 'no_escape' => !$asHtml));
        }
        catch (Omeka_Record_Exception $e)
        {
            $text = '';
        }
        return $text;
    }

    public static function getElementTextFromElementId($item, $elementId, $asHtml = true)
    {
        $db = get_db();
        $element = $db->getTable('Element')->find($elementId);
        $text = '';
        if (!empty($element))
        {
            $texts = $item->getElementTextsByRecord($element);
            $text = isset($texts[0]['text']) ? $texts[0]['text'] : '';
        }
        return $asHtml ? html_escape($text) : $text;
    }

    public static function getIdentifierAliasElementName()
    {
        $elementName = CommonConfig::getOptionTextForIdentifierAlias();
        if (empty($elementName))
            $elementName = ItemMetadata::getIdentifierElementName();
        return $elementName;
    }

    public static function getIdentifierElementName()
    {
        return CommonConfig::getOptionTextForIdentifier();
    }

    public static function getIdentifierElementId()
    {
        return self::getElementIdForElementName(self::getIdentifierElementName());
    }

    public static function getIdentifierPrefix()
    {
        return CommonConfig::getOptionTextForIdentifierPrefix();
    }

    public static function getItemFromId($id)
    {
        return get_record_by_id('Item', $id);
    }

    public static function getItemFromIdentifier($identifier)
    {
        $elementId = CommonConfig::getOptionDataForIdentifier();
        $items = get_records('Item', array('advanced' => array(array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $identifier))));
        if (empty($items))
            return null;
        return $items[0];
    }

    public static function getItemIdentifier($item)
    {
        return self::getElementTextFromElementId($item, CommonConfig::getOptionDataForIdentifier());
    }

    public static function getItemIdentifierAlias($item)
    {
        $aliasElementId = CommonConfig::getOptionDataForIdentifierAlias();
        if (empty($aliasElementId))
            $aliasText = self::getItemIdentifier($item);
        else
            $aliasText = self::getElementTextFromElementId($item, $aliasElementId);
        return $aliasText;
    }

    public static function getItemIdFromIdentifier($identifier)
    {
        $item = self::getItemFromIdentifier($identifier);
        return empty($item) ? 0 : $item->id;
    }

    public static function getItemsWithElementValue($elementId, $value)
    {
        $sql = ItemSearch::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $results = $db->query($sql)->fetchAll();
        return $results;
    }

    public static function getItemTitle($item, $asHtml = true)
    {
        return self::getElementTextFromElementId($item, self::getTitleElementId(), $asHtml);
    }

    public static function getTitleElementId()
    {
        return self::getElementIdForElementName(self::getTitleElementName());
    }

    public static function getTitleElementName()
    {
        return 'Title';
    }
}