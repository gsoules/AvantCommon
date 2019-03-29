<?php
class ItemPreview
{
    protected $item;
    protected $useElasticsearch;

    public function __construct($item, $useElasticsearch = false)
    {
        $this->item = $item;
        $this->useElasticsearch = $useElasticsearch;
    }

    public function emitItemHeader()
    {
        if ($this->useElasticsearch)
        {
            $identifier = $this->item['_source']['element']['identifier'];
        }
        else
        {
            $identifier = ItemMetadata::getItemIdentifierAlias($this->item);
        }

        $prefix = ItemMetadata::getIdentifierPrefix();

        if ($this->useElasticsearch)
        {
            $url = $this->item['_source']['url'];
            $public =  $this->item['_source']['public'];
        }
        else
        {
            $url = url("items/show/{$this->item->id}");
            $public = $this->item->public;
        }

        $html = '<div class="item-preview-header">';

        if ($public == 0)
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
        $originalImageUrl = '';
        $getThumbnail = true;

        if ($this->useElasticsearch)
        {
            $thumbnailUrl = $this->item['_source']['thumb'];
            $itemFiles = $this->item['_source']['files'];
        }
        else
        {
            $thumbnailUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
            $itemFiles = count($this->item->Files);
        }

        if (empty($thumbnailUrl))
        {
            // This item has no thumbnail presumably because the item has no image.
            $sharedItemInfo = ItemMetadata::getSharedItemAssets($this->item);
            if (!isset($sharedItemInfo['image']))
            {
                $thumbnailUrl = self::getFallbackImageUrl($this->item);
            }
            else
            {
                $thumbnailUrl = $sharedItemInfo['thumbnail'];
                $originalImageUrl = $sharedItemInfo['image'];
            }
        }
        else
        {
            $getThumbnail = false;

            if ($this->useElasticsearch)
            {
                $originalImageUrl = $this->item['_source']['image'];
            }
            else
            {
                $originalImageUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
            }
        }

        // Emit the HTML for the actual thumbnail, an external thumbnail, or a fallback thumbnail image.
        // If the thumbnail's item has more than one image, style it differently to give the user a clue
        // that this image is just one of a set.
        $class = $itemFiles > 1 ? "class='item-preview-multiple'" : "";
        $imgTag = "<img $class src='$thumbnailUrl'>";

        if (empty($originalImageUrl))
        {
            // This item has no attached or external image.
            // Use the thumbnail HTML without wrapping it in an <a> tag (so the images won't be clickable).
            $html = $imgTag;
        }
        else
        {
            // The item has a thumbnail and large image (either both attached or both external).
            // Get text for the caption that will appear at lower-right when the large image appears in the lightbox.
            if ($this->useElasticsearch)
            {
                $title = $this->item['_source']['element']['title'];
                if (is_array($title))
                {
                    $title = $title[0];
                }
                $itemNumber = $this->item['_source']['element']['identifier'];
                $itemId = $this->item['_source']['itemid'];
            }
            else
            {
                $title = ItemMetadata::getItemTitle($this->item);
                $itemNumber = ItemMetadata::getItemIdentifier($this->item);
                $itemId = $this->item->id;
            }
            $title = empty($title) ? __('[Untitled]') : $title;

            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            // Also provide the lightbox with a link to the original image and the image's item Id which jQuery will
            // expand into a link to the item.
            $html = "<a class='lightbox' href='$originalImageUrl' title='$title' itemId='$itemId' data-itemNumber='$itemNumber'>$imgTag</a>";
        }

        // Give another plugin a chance to add to the class for installation-specific custom styling.
        if ($this->useElasticsearch)
        {
            $itemType = $this->item['_source']['element']['type'];
        }
        else
        {
            $itemType = ItemMetadata::getElementTextForElementName($this->item, 'Type');
        }
        $class = apply_filters('item_thumbnail_class', 'item-img', array('itemType' => $itemType));

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
        $sharedItemAssets = array();
        if (empty($file))
        {
            // There is no file attached to this item. See if the item specifies the URL for the image of a shared item.
            $sharedItemAssets = ItemMetadata::getSharedItemAssets($item);
            if (empty($sharedItemAssets))
            {
                // There is no shared image for this item.
                return '';
            }
            else if (isset($sharedItemAssets['error']))
            {
                $message = __('The image for this shared item is not accessible at this time.');
                $message .= $sharedItemAssets['response-code'];
                $html = "<div class='shared-item-error'>$message</div><hr/>";
                return $html;
            }
        }

        // Determine the file type. External URLs are always treated as images.
        $isImage = empty($file) || substr($file->mime_type, 0, 6) == 'image/';
        if ($isImage)
        {
            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            $class = 'lightbox';
            $title = ItemMetadata::getItemTitle($item);
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

        // Cast the Id to a string to workaround logic in globals.php tag_attributes() that ignores integer values.
        $itemId = (string)$item->id;
        $itemNumber = ItemMetadata::getItemIdentifier($item);

        if (empty($file))
        {
            // Emit HTML to display an external image. Note that this method should never get called to display
            // the thumbnail for an external image on a Show page because when external images are used, no thumbs
            // are displayed (thumbs only appear when the item has more than one image). Thumbnails for external images
            // that appear in search results are emitted by emitItemThumbnail.
            $imageUrl = $sharedItemAssets['image'];
            $html = "<div class='item-file image-jpeg'>";
            $html .= "<a class='lightbox' itemId='$itemId'  data-itemNumber='$itemNumber' href='$imageUrl' title='$title' target='_blank'>";
            $html .= "<img class='full' src='$imageUrl' alt='$title' title='$title'>";
            $html .= "</a></div>";

            if (isset($sharedItemAssets['contributor']))
            {
                $contributor = __('Shared by ') . $sharedItemAssets['contributor'];
                $itemUrl = $sharedItemAssets['item-url'];
                $message = __('View Full Item');
                $html .= "<div class='shared-item-contributor'>$contributor</div><div><a class='shared-item-link' href='$itemUrl'>$message</a></div>";
            }
        }
        else
        {
            // Emit HTML to display an attached image.
            $sizeClass = $isThumbnail ? 'thumbnail' : 'fullsize';
            $html = file_markup($file, array('imageSize' => $sizeClass, 'linkAttributes' => array('class' => $class, 'title' => $title, 'itemId' => $itemId, 'data-itemNumber' => $itemNumber, 'target' => '_blank')));
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