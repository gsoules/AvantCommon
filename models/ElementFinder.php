<?php


class ElementFinder
{
    public static function fetchElementsByValue($elementId, $value)
    {
        if (empty($value))
            return;
        $db = get_db();
        $select = $db->select()
            ->from($db->ElementText)
            ->where('element_id = ?', $elementId)
            ->where('text = ?', $value)
            ->where('record_type = ?', 'Item');
        $results = $db->getTable('ElementText')->fetchObjects($select);
        return $results;
    }

    protected static function fetchItemsWithElementValue($elementId, $value)
    {
        // Escape double quotes.
        $value = str_replace('"', '\"', $value);

        $db = get_db();
        $elementTextsTable = $db->ElementTexts;
        $itemsTable = $db->Items;

        $sql = "
            SELECT $itemsTable.id
            FROM $db->Item
            INNER JOIN $db->ElementTexts ON $itemsTable.id = $elementTextsTable.record_id
            WHERE $elementTextsTable.element_id = $elementId AND $elementTextsTable.text = \"$value\"";

        return $sql;
    }

    public static function getAdvancedSearchUrl($elementId, $value)
    {
        $params['advanced'][0]['element_id'] = $elementId;
        $params['advanced'][0]['type'] = 'is exactly';
        $params['advanced'][0]['terms'] = $value;
        $queryString = http_build_query($params);
        $action = is_admin_theme() ? 'items/browse' : 'find';
        $url = url("$action?$queryString");
        return $url;
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

    public static function getItemsWithElementValue($elementId, $value)
    {
        $sql = self::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $results = $db->query($sql)->fetchAll();
        return $results;
    }

    public static function getFirstItemWithElementValue($elementId, $value)
    {
        $sql = self::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $result = $db->query($sql)->fetch();
        return $result;
    }
}