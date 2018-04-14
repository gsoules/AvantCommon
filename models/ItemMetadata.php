<?php

class ItemMetadata
{
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

    public static function getIdentifierElementName()
    {
        $parts = ItemMetadata::getPartsForIdentifierElement();
        return $parts[1];
    }

    public static function getIdentifierPrefix()
    {
        return get_option('avantcommon_identifier_prefix');
    }

    public static function getElementMetadata($item, $parts, $asHtml = true)
    {
        try
        {
            $metadata = metadata($item, array($parts[0], $parts[1]), array('no_filter' => true, 'no_escape' => !$asHtml));
        }
        catch (Omeka_Record_Exception $e)
        {
            $metadata = '';
        }
        return $metadata;
    }

    public static function getItemFromId($id)
    {
        return get_record_by_id('Item', $id);
    }

    public static function getItemFromIdentifier($identifier)
    {
        $parts = self::getPartsForIdentifierElement();
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName($parts[0], $parts[1]);
        $items = get_records('Item', array('advanced' => array(array('element_id' => $element->id, 'type' => 'is exactly', 'terms' => $identifier))));
        if (empty($items))
            return null;
        return $items[0];
    }

    public static function getItemIdentifier($item)
    {
        return self::getElementMetadata($item, self::getPartsForIdentifierElement());
    }

    public static function getItemIdentifierAlias($item)
    {
        $parts = self::getPartsForIdentifierAliasElement();
        if (empty($parts[0]))
            $parts = self::getPartsForIdentifierElement();
        return self::getElementMetadata($item, $parts);
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
        return self::getElementMetadata($item, self::getPartsForTitleElement(), $asHtml);
    }

    public static function getPartsForIdentifierElement()
    {
        $parts = explode(',', get_option('avantcommon_identifier'));
        if (empty($parts[0]))
        {
            // Provide good values in case the user configured a blank value for the identifier.
            $parts[0] = 'Dublin Core';
            $parts[1] = 'Identifier';
        }
        $parts = array_map('trim', $parts);
        return $parts;
    }

    public static function getPartsForIdentifierAliasElement()
    {
        $parts = explode(',', get_option('avantcommon_identifier_alias'));
        if (empty($parts[0]))
        {
            $parts[0] = '';
            $parts[1] = '';
        }
        $parts = array_map('trim', $parts);
        return $parts;
    }

    public static function getPartsForTitleElement()
    {
        $parts = explode(',', get_option('avantcommon_title'));
        if (empty($parts[0]))
        {
            // Provide good values in case the user configured a blank value for the title.
            $parts[0] = 'Dublin Core';
            $parts[1] = 'Title';
        }
        $parts = array_map('trim', $parts);
        return $parts;
    }

    public static function getTitleElementId()
    {
        return self::getElementIdForElementName(self::getTitleElementName());
    }

    public static function getTitleElementName()
    {
        $parts = ItemMetadata::getPartsForTitleElement();
        return $parts[1];
    }
}