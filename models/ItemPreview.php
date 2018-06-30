<?php
class ItemPreview
{
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
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

        $externalImages = self::getExtenalImages($this->item);

        if (empty($thumbnailUrl))
        {
            $thumbnailUrl = empty($externalImages) ? self::getFallbackImageUrl($this->item) : $externalImages['thumbnail'];
        }

        $getThumbnail = false;
        $originalImageUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);

        $imgTag = "<img src='$thumbnailUrl'>";

        if (empty($originalImageUrl) && !empty($externalImages))
        {
            $originalImageUrl = $externalImages['image'];
        }

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

    protected static function getExtenalImages($item)
    {
        // Determine if external URLs are specified for this item's thumbnail and image.
        // If both are specified, return them. Otherwise return neither.

        $images = array();
        $externalImageIds = json_decode(get_option('avantcommon_external_images'), true);

        if (count($externalImageIds) == 2)
        {
            $thumbnailUrl = ItemMetadata::getElementTextFromElementId($item, $externalImageIds[0], false);
            $imageUrl = ItemMetadata::getElementTextFromElementId($item, $externalImageIds[1], false);

            if (!empty($thumbnailUrl && !empty($imageUrl)))
            {
                $images['thumbnail'] = $thumbnailUrl;
                $images['image'] = $imageUrl;
            }
        }

        return $images;
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
        $externalImages = array();
        if (empty($file))
        {
            // There is no file attached to this item. See if the item specifies the URL for an external image.
            $externalImages = self::getExtenalImages($item);
            if (empty($externalImages))
            {
                // There no attached or external file for this item.
                return '';
            }
        }

        // Determine the file type. External URLs are always treated as images.
        $isImage = empty($file) || substr($file->mime_type, 0, 6) == 'image/';
        if ($isImage)
        {
            // Include the image in the lightbox by simply attaching the 'lightbox' class to its enclosing <a> tag.
            $class = 'lightbox';
            $title = empty($file) ? $externalImages['image'] : basename($file->filename);
        }
        else
        {
            // The file is not an image. See if it's a PDF, and if not, just call it a document (e.g. a text file).
            $isPdfFile = substr($file->mime_type, 0, 15) == 'application/pdf';
            $class = $isPdfFile ? 'pdf-icon' : 'document-icon';
            if ($isThumbnail)
                $class .= '-thumb';
            $title = '';
        }

        if (empty($file))
        {
            // Emit HTML to display an external image. Note that this method should never get called to display
            // the thumbnail for an external image on a Show page because when external images are used, no thumbs
            // are displayed (thumbs only appear when the item has more than one image). Thumbnails for external images
            // that appear in search results are emitted by emitItemThumbnail.
            $url = $externalImages['image'];
            $html = "<div class='item-file image-jpeg'>";
            $html .= "<a id='' class='lightbox' href='$url' title='$title' target='_blank'>";
            $html .= "<img class='full' src='$url' alt='$title' title='$title'>";
            $html .= "</a></div>";
        }
        else
        {
            // Emit HTML to display an attached image.
            $sizeClass = $isThumbnail ? 'thumbnail' : 'fullsize';
            $html = file_markup($file, array('imageSize' => $sizeClass, 'linkAttributes' => array('class' => $class, 'title' => $title, 'id' => '', 'target' => '_blank')));
        }

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