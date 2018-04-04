<?php

class ItemView
{
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function emitItemHeader($useCoverImage = false)
    {
        $html = apply_filters('item_thumbnail_header', '', array('item' => $this->item, 'use_cover_image' => $useCoverImage));
        return $html;
    }

    public function emitItemPreview($useCoverImage = true)
    {
        $html = $this->emitItemHeader($useCoverImage);
        $html .= "<div>";
        $html .= $this->emitItemThumbnail($useCoverImage);
        $html .= $this->emitItemTitle();
        $html .= "</div>";
        return $html;
    }

    public function emitItemPreviewAsListElement($useCoverImage = true, $attributes = '')
    {
        $html = "<li $attributes>";
        $html .= $this->emitItemPreview($useCoverImage);
        $html .= "</li>";
        return $html;
    }

    public function emitItemThumbnail($useCoverImage = true)
    {
        $getThumbnail = true;
        $url = ItemView::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        if (empty($url))
        {
            $url = self::getFallbackImageUrl($this->item);
        }

        $title = ItemView::getItemTitle($this->item);
        $imgTag = "<img src='$url' alt='$title' title='$title'>";
        $html = link_to_item($imgTag, array(), null, $this->item);
        $class = apply_filters('item_thumbnail_class', 'item-img', array('item' => $this->item));
        $html = "<div class=\"$class\">$html</div>";

        return $html;
    }

    public function emitItemTitle($showIdentifier = false)
    {
        $title = self::getItemTitle($this->item);
        $identifier = '';
        if ($showIdentifier)
        {
            $identifier = " (<span class=\"related-item-identifier\">{self::getItemIdentifier($this->item)}</span>)";
        }
        $url = url("items/show/{$this->item->id}");
        $html = "<div class=\"element-text\"><a href=\"$url\">$title</a>$identifier</div>";
        return $html;
    }

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

    public static function getCoverImageIdentifier($itemId)
    {
        if (!plugin_is_active('AvantRelationships'))
            return '';

        $db = get_db();
        return $db->getTable('RelationshipImages')->getImageItemIdentifier($itemId);
    }

    public static function getCoverImageItem($item)
    {
        $coverImageIdentifier = self::getCoverImageIdentifier($item->id);

        if (!$coverImageIdentifier)
            return null;

        $coverImageItem = self::getItemFromIdentifier($coverImageIdentifier);
        return $coverImageItem ? $coverImageItem : null;
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

    public static function getFallbackImageUrl($item)
    {
        $defaultFallbackImageFileName = 'fallback-file.png';
        $fallbackImageFilename = apply_filters('fallback_image_name', $defaultFallbackImageFileName, array('item' => $item));

        try
        {
            $url = img($fallbackImageFilename);
        }
        catch (InvalidArgumentException $e)
        {
            $url = img($defaultFallbackImageFileName);
        }

        return $url;
    }

    public static function getFirstItemWithElementValue($elementId, $value)
    {
        $sql = self::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $result = $db->query($sql)->fetch();
        return $result;
    }

    public static function getIdentifierElementName()
    {
        $parts = ItemView::getPartsForIdentifierElement();
        return $parts[1];
    }

    public static function getImageUrl($item, $useCoverImage, $thumbnail = false)
    {
        $coverImageIdentifier = self::getCoverImageIdentifier($item->id);
        $coverImageItem = empty($coverImageIdentifier) ? null : ItemView::getItemFromIdentifier($coverImageIdentifier);

        $itemImageUrl = self::getItemFileUrl($item, $thumbnail);
        $coverImageUrl = empty($coverImageItem) ? '' : self::getItemFileUrl($coverImageItem, $thumbnail);

        if ($useCoverImage)
        {
            // Use the cover image unless its empty, in which case use the item's image.
            $url = !empty($coverImageUrl) ? $coverImageUrl : $itemImageUrl;
        }
        else
        {
            // Use the the item's image unless its empty, in which case use the cover image even though
            // the request was not to use it; however, better to show the cover image than nothing.
            $url = !empty($itemImageUrl) ? $itemImageUrl : $coverImageUrl;
        }

        return $url;
    }

    private static function getItemElementMetadata($item, $parts, $asHtml = true)
    {
        try
        {
            $metadata = metadata($item, array($parts[0], $parts[1]), array('no_filter' => true, 'no_escape' => !$asHtml));
        }
        catch (Omeka_Record_Exception $e)
        {
            // The user configured a bad value for the identifier or title.
            $metadata = '???';
        }
        return $metadata;
    }

    protected static function getItemFileUrl($item, $thumbnail = false)
    {
        $url = '';
        $file = $item->getFile(0);
        if (!empty($file) && $file->hasThumbnail())
        {
            $url = $file->getWebPath($thumbnail ? 'thumbnail' : 'original');
            if (strlen($url) > 4 && strpos(strtolower($url), '.jpg', strlen($url) - 4) === false)
            {
                // The original image is not a jpg (it's probably a pdf) so return its derivative image instead.
                $url = $file->getWebPath($thumbnail ? 'thumbnail' : 'fullsize');
            }
        }
        return $url;
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
        return self::getItemElementMetadata($item, self::getPartsForIdentifierElement());
    }

    public static function getItemIdFromIdentifier($identifier)
    {
        $item = self::getItemFromIdentifier($identifier);
        return empty($item) ? 0 : $item->id;
    }

    public static function getItemImageUri(Item $item)
    {
        $file = $item->getFile();

        if ($file && $file->hasThumbnail())
        {
            $uri = $file->getWebPath('thumbnail');
        }
        else
        {
            $uri = self::getFallbackImageUrl($item);
        }
        return $uri;
    }

    public static function getItemsWithElementValue($elementId, $value)
    {
        $sql = self::fetchItemsWithElementValue($elementId, $value);
        $db = get_db();
        $results = $db->query($sql)->fetchAll();
        return $results;
    }

    public static function getItemTitle($item, $asHtml = true)
    {
        return self::getItemElementMetadata($item, self::getPartsForTitleElement(), $asHtml);
    }

    public static function getPartsForIdentifierElement()
    {
        $parts = explode(',', get_option('common_identifier'));
        if (empty($parts[0]))
        {
            // Provide good values in case the user configured a blank value for the identifier.
            $parts[0] = 'Dublin Core';
            $parts[1] = 'Identifier';
        }
        $parts = array_map('trim', $parts);
        return $parts;
    }

    public static function getPartsForTitleElement()
    {
        $parts = explode(',', get_option('common_title'));
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
        $parts = ItemView::getPartsForTitleElement();
        return $parts[1];
    }
}