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

    public function emitItemThumbnail($useCoverImage = true)
    {
        $getThumbnail = true;
        $url = ItemView::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        if (empty($url))
        {
            $fallbackImageFilename = apply_filters('fallback_image_name', 'fallback-file.png', array('item' => $this->item));
            $url = img("$fallbackImageFilename");
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

    public static function getCoverImageIdentifier($itemId)
    {
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

    public static function getFallbackImageUrl($item)
    {
        $fallbackImageFilename = apply_filters('fallback_image_name', 'fallback-file.png', array('item' => $item));
        return img($fallbackImageFilename);
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
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Identifier');
        $items = get_records('Item', array('advanced' => array(array('element_id' => $element->id, 'type' => 'is exactly', 'terms' => $identifier))));
        if (empty($items))
            return null;
        return $items[0];
    }

    public static function getItemIdFromIdentifier($identifier)
    {
        $item = self::getItemFromIdentifier($identifier);
        return empty($item) ? 0 : $item->id;
    }

    public static function getItemIdentifier($item)
    {
        return metadata($item, array('Dublin Core', 'Identifier'), array('no_filter' => true));
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

    public static function getItemTitle($item)
    {
        return metadata($item, array('Dublin Core', 'Title'), array('no_filter' => true));
    }
}