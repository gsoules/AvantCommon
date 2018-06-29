<?php
class ItemPreview
{
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    protected static function getSharedImages($item)
    {
        $imagesElementText = ItemMetadata::getElementTextForElementName($item, 'Images');
        $images = array();
        if (strlen($imagesElementText) > 0)
        {
            $images = explode('|', $imagesElementText);
            if (count($images) == 1)
            {
                $images[] = $images[0];
            }
        }
        return $images;
    }

    public function emitItemHeader()
    {
        $identifier = ItemMetadata::getItemIdentifierAlias($this->item);

        $prefix = ItemMetadata::getIdentifierPrefix();

        $url = url("items/show/{$this->item->id}");

        $html = '<div class="item-preview-header">';
        if ($this->item->public == 0)
        {
            // Indicate that this item is private.
            $html .= '* ';
        }
        $html .= "<a class='item-preview-identifier' href=\"$url\">{$prefix}{$identifier}</a>";
        $html .= '</div>';
        return $html;
    }

    public function emitItemPreview($useCoverImage = true)
    {
        $html = $this->emitItemHeader();
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
        $thumbnailUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        if (empty($thumbnailUrl))
        {
            $thumbnailUrl = self::getFallbackImageUrl($this->item);
        }

        $getThumbnail = false;
        $originalImageUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        //////////////
        $sharedImages = self::getSharedImages($this->item);
        if (!empty($sharedImages))
        {
            $thumbnailUrl = $sharedImages[0];
            $originalImageUrl = $sharedImages[1];
        }
        /////////////

        $imgTag = "<img src='$thumbnailUrl'>";

        if (empty($originalImageUrl))
        {
            $html = $imgTag;
        }
        else
        {
            $caption = ItemMetadata::getItemTitle($this->item);
            $caption = empty($caption) ? __('[Untitled]') : $caption;
            $html = "<a class='lightbox' href='$originalImageUrl' title='$caption' id='{$this->item->id}'>$imgTag</a>";
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

        if (is_admin_theme() || $item == null)
            $fallbackImageFilename = '';
        else
            $fallbackImageFilename = apply_filters('fallback_image_name', $defaultFallbackImageFileName, array('item' => $item));

        if (empty($fallbackImageFilename))
            $fallbackImageFilename = $defaultFallbackImageFileName;

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

    public static function getFileHtml($item, $file, $isThumbnail)
    {
        $sizeClass = $isThumbnail ? 'thumbnail' : 'fullsize';
        $isImageFile = substr($file->mime_type, 0, 6) == 'image/';

        if ($isImageFile)
        {
            $class = 'lightbox';
            $title = basename($file->filename);
        }
        else
        {
            $isPdfFile = substr($file->mime_type, 0, 15) == 'application/pdf';
            $class = $isPdfFile ? 'pdf-icon' : 'document-icon';
            if ($isThumbnail)
                $class .= '-thumb';
            $title = '';
        }

        $html = file_markup($file, array('imageSize' => $sizeClass, 'linkAttributes' => array('class' => $class, 'title' => $title, 'id' => '', 'target' => '_blank')));

        /////////////
        $sharedImages = self::getSharedImages($item);
        if (!empty($sharedImages))
        {
            $src = $sharedImages[1];
            $href = $src;
            $html = '<div class="item-file image-jpeg"><a class="lightbox" href="' . $href . '" title="5801.jpg" id="" target="_blank"><img class="full" src="' . $src. '" alt="5801.jpg" title="5801.jpg"></a></div>';
        }
        /////////////

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