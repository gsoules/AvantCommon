<?php
class ItemSearch
{
    public static function fetchItemsWithElementValue($elementId, $value)
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

        $useOmekaSearch = is_admin_theme() | !plugin_is_active('AvantSearch');
        $action = $useOmekaSearch ? 'items/browse' : 'find';

        return url("$action?$queryString");
    }

    public static function getFirstItemWithElementValue($elementId, $value)
    {
        $sql = ItemSearch::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $result = $db->query($sql)->fetch();
        return $result;
    }
}