<?php

define('IMAGE_THUMB_TOOLTIP', __('Show larger image of '));
define('PDF_THUMB_TOOLTIP', __('Read this PDF file '));
define('ITEM_LINK_TOOLTIP', __('View this item'));

class ItemPreview
{
    protected $item;
    protected $sharedSearchingEnabled;
    protected $useElasticsearch;

    public function __construct($item, $useElasticsearch = false, $sharedSearchingEnabled = false)
    {
        $this->item = $item;
        $this->useElasticsearch = $useElasticsearch;
        $this->sharedSearchingEnabled = $sharedSearchingEnabled;
    }

    public function emitItemHeader($openLinkInNewWindow = false)
    {
        if ($this->useElasticsearch)
        {
            $itemId = $this->item['_source']['item']['id'];
            $contributorId = $this->item['_source']['item']['contributor-id'];
            $identifier = $this->item['_source']['core-fields']['identifier'][0];
            $identifier = "$contributorId-$identifier";
            $url = $this->item['_source']['url']['item'];
            $public =  $this->item['_source']['item']['public'];
        }
        else
        {
            $itemId = $this->item->id;
            $identifier = ItemMetadata::getItemIdentifierAlias($this->item);
            $url = url("items/show/$itemId");
            $public = $this->item->public;
        }

        $html = '<div class="item-preview-header">';

        if ($public == 0)
        {
            // Indicate that this item is private.
            $html .= PRIVATE_ITEM_PREFIX;
        }

        $prefix = ItemMetadata::getIdentifierPrefix();
        $tooltip = ITEM_LINK_TOOLTIP;
        $target = $openLinkInNewWindow ? " target='_blank'" : '';
        $html .= "<a class='item-preview-identifier' href='$url' title='$tooltip'{$target}>{$prefix} {$identifier}</a>";
        $isLocalItem = $this->item['_source']['item']['contributor-id'] == ElasticsearchConfig::getOptionValueForContributorId();
        if ($isLocalItem)
        {
            // Only show identifier when viewing the local site since identifiers are not unique across sites.
            $html .= AvantCommon::emitFlagItemAsRecent($itemId, AvantCommon::getRecentlyViewedItemIds());
        }
        $html .= '</div>';
        return $html;
    }

    protected static function getImageLinkHtml($itemId, $itemNumber, $class, $imageUrl, $thumbUrl, $title, $tooltip, $isForeign, $index)
    {
        // Note that specifying an empty string for data-itemUrl causes the browser to use the current page's URL.
        $html = "<div class='item-file image-jpeg'>";
        $html .= "<a class='$class' href='$imageUrl' title='$tooltip' itemId='$itemId' data-title='$title' data-itemNumber='$itemNumber' data-itemUrl='' data-foreign='$isForeign' target='_blank'>";
        $html .= "<img class='full' src='$thumbUrl'>";
        $html .= "</a>";

        if ($index > 0)
            $html .= "<div class='item-file-index'>$index</div>";

        $html .= "</div>";
        return $html;
    }

    public static function getItemLinkTooltip()
    {
        return ITEM_LINK_TOOLTIP;
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

    public function emitItemPreviewAsRow($attributes)
    {
        $html = "<div{$attributes}>";
        $html .= $this->emitItemHeader();
        $html .= "<div>";
        $html .= $this->emitItemTitle();
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    public function emitItemPreviewForGrid($sharedSearchingEnabled)
    {
        $contributorId = $sharedSearchingEnabled ? ' <span class="contributor-id">' . $this->item['_source']['item']['contributor'] . '</span>' : '';

        $html = "<div class='grid-view-cell'>";
        $html .= $this->emitItemHeader(true);
        $html .= $this->emitItemThumbnail(true);
        $html .= $this->emitItemTitle(true, $contributorId);
        $html .= "</div>";
        return $html;
    }

    public function emitItemThumbnail($useCoverImage = true)
    {
        $originalImageUrl = '';
        $getThumbnail = true;
        $isFallbackImage = false;

        if ($this->useElasticsearch)
        {
            $thumbnailUrl = isset($this->item['_source']['url']['thumb']) ? $this->item['_source']['url']['thumb'] : '';
            $fileCount = $this->item['_source']['file']['total'];
            $isCoverImage = isset($this->item['_source']['url']['cover']) ?  $this->item['_source']['url']['cover'] : false;
        }
        else
        {
            $thumbnailUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
            $fileCount = count($this->item->Files);
            $isCoverImage = !empty(self::getCoverImageIdentifier($this->item->id));

        }

        if (empty($thumbnailUrl))
        {
            // This item has no thumbnail presumably because the item has no image.
            $thumbnailUrl = self::getFallbackImageUrl($this->item, $this->useElasticsearch);
            $isFallbackImage = true;
        }
        else
        {
            $getThumbnail = false;

            if ($this->useElasticsearch)
            {
                $originalImageUrl = isset($this->item['_source']['url']['image']) ? $this->item['_source']['url']['image'] : '';
            }
            else
            {
                $originalImageUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
            }
        }

        // Emit the HTML for the actual thumbnail, an external thumbnail, or a fallback thumbnail image.
        $class = '';
        if ($fileCount > 1)
        {
            // Style this item to indicate that it has more than one file attached to it.
            $class = "class='item-preview-multiple'";
        }
        else if ($isCoverImage)
        {
            // Style this item to indicate that its cover image belongs to another item.
            $class = "class='item-preview-cover'";
        }

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
                $title = isset($this->item['_source']['core-fields']['title']) ? $this->item['_source']['core-fields']['title'][0] : UNTITLED_ITEM;
                if (is_array($title))
                {
                    $title = $title[0];
                }
                $itemNumber = $this->item['_source']['core-fields']['identifier'][0];
                $itemId = $this->item['_source']['item']['id'];
            }
            else
            {
                $title = ItemMetadata::getItemTitle($this->item);
                $itemNumber = ItemMetadata::getItemIdentifier($this->item);
                $itemId = $this->item->id;
            }

            $title = empty($title) ? UNTITLED_ITEM : $title;

            // Escape single quotes since the title ends up getting into Javascript via the data-title attribute.
            $title = str_replace("'", '&#39;', $title);

            // Determine if this item was contributed by this installation or by another.
            $isForeign = $this->sharedSearchingEnabled && $this->item['_source']['item']['contributor-id'] != ElasticsearchConfig::getOptionValueForContributorId();
            $isForeign = $isForeign ? '1' : '0';

            $itemUrl = $this->sharedSearchingEnabled ? $this->item['_source']['url']['item'] : url("items/show/$itemId");

            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            // Also provide the lightbox with a link to the original image and the image's item Id which jQuery will
            // expand into a link to the item.
            $fileName = basename($originalImageUrl);
            $tooltip = IMAGE_THUMB_TOOLTIP . $fileName;
            $html = "<a class='lightbox' href='$originalImageUrl' title='$tooltip' itemId='$itemId' data-title='$title' data-itemNumber='$itemNumber' data-itemUrl='$itemUrl' data-foreign='$isForeign'>$imgTag</a>";
        }

        // Give another plugin a chance to add to the class for installation-specific custom styling.
        if ($this->useElasticsearch)
        {
            if (isset($this->item['_source']['core-fields']['type']))
            {
                $itemType = $this->item['_source']['core-fields']['type'][0];
            }
            else
            {
                $itemType = 'UNKNOWN TYPE';
            }
        }
        else
        {
            $itemType = ItemMetadata::getElementTextForElementName($this->item, 'Type');
        }
        $class = apply_filters('item_thumbnail_class', 'item-img', array('itemType' => $itemType));

        if ($isFallbackImage)
        {
            $class .= ' fallback-image';
        }

        $html = "<div class=\"$class\">$html</div>";

        return $html;
    }

    public function emitItemTitle($openInNewWindow = false, $contributorId = '')
    {
        if ($this->useElasticsearch)
        {
            $element = $this->item['_source']['core-fields'];
            $title = isset($element["title"]) ? $element["title"][0] : UNTITLED_ITEM;
            $url = $this->item['_source']['url']['item'];
        }
        else
        {
            $title = ItemMetadata::getItemTitle($this->item);
            $id = $this->item->id;
            $url = url("items/show/$id");
        }

        $tooltip = ITEM_LINK_TOOLTIP;
        $target = $openInNewWindow ? " target='_blank'" : '';
        $html = "<div class=\"element-text\"><a href='$url' title='$tooltip' $target>$title</a>$contributorId</div>";
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

    public static function getFallbackImageUrl($item, $useElasticsearch = false)
    {
        $defaultFallbackImageFileName = 'fallback-file.png';

        if (is_admin_theme() || $item == null)
        {
            $fallbackImageFilename = '';
        }
        else
        {
            if ($useElasticsearch)
            {
                $typeName = isset($item['_source']['core-fields']['type']) ? $item['_source']['core-fields']['type'][0] : '';
                $subject = isset($item['_source']['core-fields']['subject']) ? $item['_source']['core-fields']['subject'][0] : '';
                if (is_array($subject))
                {
                    $subject = $subject[0];
                }
            }
            else
            {
                $typeName = ItemMetadata::getElementTextForElementName($item, 'Type');
                $subject = ItemMetadata::getElementTextForElementName($item, 'Subject');
            }

            $fallbackImageFilename = apply_filters('fallback_image_name', $defaultFallbackImageFileName, array('typeName' => $typeName, 'subject' => $subject));
        }

        if (empty($fallbackImageFilename))
        {
            $fallbackImageFilename = $defaultFallbackImageFileName;
        }

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

    public static function getFileHtml($item, $file, $isThumbnail, $index = 0)
    {
        if (empty($file))
        {
            return '';
        }

        // Determine the file type. External URLs are always treated as images.
        $isImage = empty($file) || substr($file->mime_type, 0, 6) == 'image/';
        if ($isImage)
        {
            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            $isPdfFile = false;
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
        $tooltip = $isPdfFile ? PDF_THUMB_TOOLTIP : IMAGE_THUMB_TOOLTIP;

        // Emit HTML to display an attached image.
        $isForeign = '0';
        $imageUrl = $file->getWebPath('original');

        // Convert any backslashes to forward slashes for subsequent logic that expects all forward slashes.
        $imageUrl = str_replace("\\", "/", $imageUrl);

        $thumbUrl = $file->getWebPath($isThumbnail ? 'thumbnail' : 'fullsize');
        $tooltip .= basename($imageUrl);
        $html = self::getImageLinkHtml($itemId, $itemNumber, $class, $imageUrl, $thumbUrl, $title, $tooltip, $isForeign, $index);

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

    public static function getItemFileUrl($item, $thumbnail = false)
    {
        $url = '';
        $file = $item->getFile(0);
        if (!empty($file) && $file->hasThumbnail())
        {
            $url = $file->getWebPath($thumbnail ? 'thumbnail' : 'original');

            $supportedImageMimeTypes = AvantCommon::supportedImageMimeTypes();

            if (!in_array($file->mime_type, $supportedImageMimeTypes))
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