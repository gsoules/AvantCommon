<?php
class ItemPreview
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
        $url = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        if (empty($url))
        {
            $url = self::getFallbackImageUrl($this->item);
        }

        $title = ItemMetadata::getItemTitle($this->item);
        $imgTag = "<img src='$url'>";
        $url = self::getImageUrl($this->item, true);
        if (empty($url))
        {
            $html = $imgTag;
        }
        else
        {
            $html = "<a class='lightbox' href='$url'>$imgTag</a>";
        }
        $class = apply_filters('item_thumbnail_class', 'item-img', array('item' => $this->item));
        $html = "<div class=\"$class\">$html</div>";

        return $html;
    }

    public function emitItemTitle()
    {
        $title = ItemMetadata::getItemTitle($this->item);
        $identifier = '';
        $url = url("items/show/{$this->item->id}");
        $html = "<div class=\"element-text\"><a href=\"$url\">$title</a>$identifier</div>";
        return $html;
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

        $coverImageItem = ItemMetadata::getItemFromIdentifier($coverImageIdentifier);
        return $coverImageItem ? $coverImageItem : null;
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

    public static function getFileHtml($file, $isThumbnail)
    {
        $sizeClass = $isThumbnail ? 'thumbnail' : 'fullsize';
        $isImageFile = substr($file->mime_type, 0, 6) == 'image/';

        if ($isImageFile)
        {
            $class = 'lightbox';
        }
        else
        {
            $isPdfFile = substr($file->mime_type, 0, 15) == 'application/pdf';
            $class = $isPdfFile ? 'pdf-icon' : 'document-icon';
            if ($isThumbnail)
                $class .= '-thumb';
        }

        $html = file_markup($file, array('imageSize' => $sizeClass, 'linkAttributes' => array('class' => $class, 'target' => '_blank')));
        return $html;
    }

    public static function getImageUrl($item, $useCoverImage, $thumbnail = false)
    {
        $coverImageIdentifier = self::getCoverImageIdentifier($item->id);
        $coverImageItem = empty($coverImageIdentifier) ? null : ItemMetadata::getItemFromIdentifier($coverImageIdentifier);

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
}