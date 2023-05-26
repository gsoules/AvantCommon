<?php

define('FALLBACK_THUMB_TOOLTIP', __('Click title to view item'));
define('IMAGE_THUMB_TOOLTIP', __('See larger image (click title to view item)'));
define('IMAGE_TOOLTIP', __('See larger image'));
define('PDF_THUMB_TOOLTIP', __('Read this PDF file'));
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

    protected static function emitImageLinkHtml($class, $imageUrl, $tooltip, $itemId, $title, $itemNumber, $itemUrl, $isForeign, $contributor, $pdfUrl, $imgTag)
    {
        $html = "<a class='$class' href='$imageUrl' itemId='$itemId' alt='$title' ";
        $html .= "data-title='$title' data-itemNumber='$itemNumber' data-itemUrl='$itemUrl' data-tooltip='$tooltip' ";
        $html .= "data-foreign='$isForeign' data-contributor='$contributor' data-pdf='$pdfUrl'>$imgTag</a>";
        return $html;
    }

    public function emitItemHeader($openLinkInNewWindow = false)
    {
        if ($this->useElasticsearch)
        {
            $itemId = $this->item['_source']['item']['id'];
            $contributorId = $this->sharedSearchingEnabled ? $this->item['_source']['item']['contributor-id'] . '-' : '';
            $identifier = $this->item['_source']['core-fields']['identifier'][0];
            $identifier = $contributorId . $identifier;
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
        $tooltip = AvantCommon::getCustomText('item_link_tooltip', ITEM_LINK_TOOLTIP);
        $target = $openLinkInNewWindow ? " target='_blank'" : '';
        $html .= "<a class='item-preview-identifier' href='$url' data-tooltip='$tooltip'{$target}>{$prefix} {$identifier}</a>";
        $isLocalItem =  !$this->useElasticsearch || $this->item['_source']['item']['contributor-id'] == ElasticsearchConfig::getOptionValueForContributorId();
        if ($isLocalItem)
        {
            // Only show identifier when viewing the local site since identifiers are not unique across sites.
            $html .= AvantCommon::emitFlagItemAsRecent($itemId, AvantCommon::getRecentlyViewedItemIds());
        }
        $html .= '</div>';
        return $html;
    }

    public static function getImageLinkHtml($itemId, $itemNumber, $class, $imageUrl, $thumbUrl, $pdfUrl, $title, $tooltip, $isForeign, $index)
    {
        $imgTag = "<img class='full' src='$thumbUrl'>";
        $itemUrl = '';
        $contributor = '';

        $html = "<div class='item-file image-jpeg'>";
        $html .= self::emitImageLinkHtml($class, $imageUrl, $tooltip, $itemId, $title, $itemNumber, $itemUrl, $isForeign, $contributor, $pdfUrl, $imgTag);

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
        $pdfUrl = '';
        $getThumbnail = true;
        $isFallbackImage = false;

        if ($this->useElasticsearch)
        {
            $source = $this->item['_source'];
            $thumbnailUrl = isset($this->item['_source']['url']['thumb']) ? $this->item['_source']['url']['thumb'] : '';
            $fileCount = $this->item['_source']['file']['total'];
            $hasCoverImage = isset($this->item['_source']['url']['cover']) ? $this->item['_source']['url']['cover'] : false;
        }
        else
        {
            $source = null;
            $thumbnailUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
            $fileCount = count($this->item->Files);
            $hasCoverImage = !empty(self::getCoverImageIdentifier($this->item->id));
        }

        if (!empty($thumbnailUrl) && AvantCommon::isRemoteImageUrl($thumbnailUrl))
        {
            // Verify that the remote image exists on the remote image server.
            if (!AvantCommon::remoteImageExists($thumbnailUrl))
                $thumbnailUrl = null;
        }

        // Temporary until it's possible to identify an audio file from Elasticsearch.
        $file = $this->useElasticsearch ? null : $this->item->getFile(0);
        $isAudio = $file != null && $file->mime_type != null && substr($file->mime_type, 0) == 'audio/mpeg';

        if (empty($thumbnailUrl) || $isAudio)
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
                if (isset($source['pdf']['file-url'][0]))
                    $pdfUrl = $source['pdf']['file-url'][0];
                $originalImageUrl = isset($source['url']['image']) ? $this->item['_source']['url']['image'] : '';
            }
            else
            {
                $originalImageUrl = self::getImageUrl($this->item, $useCoverImage, $getThumbnail);
                $isPdfFile = $file && substr($file->mime_type, 0, 15) == 'application/pdf';
                if ($isPdfFile)
                    $pdfUrl = $file->getWebPath('original');
            }
        }

        // Emit the HTML for the actual thumbnail, an external thumbnail, or a fallback thumbnail image.
        $class = '';
        if ($fileCount > 1)
        {
            // Style this item to indicate that it has more than one file attached to it.
            $class = "class='item-preview-multiple'";
        }
        else if ($hasCoverImage)
        {
            // Always show the icon for a reference item or item set that has a cover image.
            if ($this->useElasticsearch)
                $itemType = $source['core-fields']['type'][0];
            else
                $itemType = ItemMetadata::getElementTextForElementName($this->item, 'Type');

            $isItemSet = strpos($itemType, 'Set') === 0 && plugin_is_active('AvantRelationships');

            // Style this item to indicate that its cover image belongs to another item.
            $class = $isItemSet ? "class='item-preview-cover-set'" :  "class='item-preview-cover'";
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
            $contributor = '';
            if ($this->useElasticsearch)
            {
                $source = $this->item['_source'];
                $title = isset($source['core-fields']['title']) ? $source['core-fields']['title'][0] : UNTITLED_ITEM;
                if (is_array($title))
                {
                    $title = $title[0];
                }
                $itemNumber = $source['core-fields']['identifier'][0];
                $itemId = $source['item']['id'];
                $contributor = $this->sharedSearchingEnabled ? $source['item']['contributor'] : '';
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
            $isForeign = $this->sharedSearchingEnabled && $source['item']['contributor-id'] != ElasticsearchConfig::getOptionValueForContributorId();
            $isForeign = $isForeign ? '1' : '0';

            $itemUrl = $this->sharedSearchingEnabled ? $source['url']['item'] : url("items/show/$itemId");

            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            // Also provide the lightbox with a link to the original image and the image's item Id which jQuery will
            // expand into a link to the item.
            $tooltip = AvantCommon::getCustomText('image_thumb_tooltip', IMAGE_THUMB_TOOLTIP);
            $class = 'lightbox';
            $html = self::emitImageLinkHtml($class, $originalImageUrl, $tooltip, $itemId, $title, $itemNumber, $itemUrl, $isForeign, $contributor, $pdfUrl, $imgTag);
        }

        // Give another plugin a chance to add to the class for installation-specific custom styling.
        if ($this->useElasticsearch)
        {
            if (isset($source['core-fields']['type']))
            {
                $itemType = $source['core-fields']['type'][0];
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

        $tooltipData = '';
        if ($isFallbackImage)
        {
            $class .= ' fallback-image';
            $tooltipData = " data-tooltip='" . AvantCommon::getCustomText('fallback_thumb_tooltip', FALLBACK_THUMB_TOOLTIP) . "'";
        }

        $html = "<div class=\"$class\"$tooltipData>$html</div>";

        return $html;
    }

    public function emitItemTitle($openInNewWindow = false, $contributorId = '')
    {
        if ($this->useElasticsearch)
        {
            $source = $this->item['_source'];
            $element = $source['core-fields'];
            $title = isset($element["title"]) ? $element["title"][0] : UNTITLED_ITEM;
            $url = $this->item['_source']['url']['item'];
        }
        else
        {
            $title = ItemMetadata::getItemTitle($this->item);
            $id = $this->item->id;
            $url = url("items/show/$id");
        }

        $tooltip = AvantCommon::getCustomText('item_link_tooltip', ITEM_LINK_TOOLTIP);
        $target = $openInNewWindow ? " target='_blank'" : '';
        $html = "<div class=\"element-text\"><a href='$url' data-tooltip='$tooltip' $target>$title</a>$contributorId</div>";
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

        // Determine the file type.
        $isImage = empty($file) || substr($file->mime_type, 0, 6) == 'image/';
        $isAudio = !$isImage && substr($file->mime_type, 0) == 'audio/mpeg';

        if ($isImage)
        {
            // Include the image in the lightbox by simply attaching the 'lightbox' class to the enclosing <a> tag.
            $isPdfFile = false;
            $class = 'lightbox';
            $title = ItemMetadata::getItemTitle($item);
        }
        else if (!$isAudio)
        {
            // The file is not an image or audio. See if it's a PDF, and if not, just call it a document (e.g. a text file).
            $isPdfFile = substr($file->mime_type, 0, 15) == 'application/pdf';
            $class = $isPdfFile ? 'pdf-icon' : 'document-icon';
            if ($isThumbnail)
                $class .= '-thumb';
            $title = '';
        }

        // Convert any backslashes in the URL to forward slashes for subsequent logic that expects all forward slashes.
        $url = $file->getWebPath('original');
        $url = str_replace("\\", "/", $url);

        if ($isAudio)
        {
            $fileName = $file->original_filename;
            $html = "<div class='audio-file-name'>$fileName</div><audio controls><source type='audio/mpeg' src='$url'></audio>";
        }
        else
        {
            // Cast the Id to a string to workaround logic in globals.php tag_attributes() that ignores integer values.
            $itemId = (string)$item->id;

            $itemNumber = ItemMetadata::getItemIdentifier($item);
            $isForeign = '0';
            $pdfUrl = '';
            $thumbUrl = $file->getWebPath($isThumbnail ? 'thumbnail' : 'fullsize');

            $tooltip = $isPdfFile ? AvantCommon::getCustomText('pdf_thumb_tooltip', PDF_THUMB_TOOLTIP) : AvantCommon::getCustomText('image_tooltip', IMAGE_TOOLTIP);

            $html = self::getImageLinkHtml($itemId, $itemNumber, $class, $url, $thumbUrl, $pdfUrl, $title, $tooltip, $isForeign, $index);
        }

        return $html;
    }

    protected static function getHybridItemImageUrl($itemId, $thumbnail)
    {
        $url = null;
        if (plugin_is_active('AvantHybrid'))
        {
            $hybridImageRecords = AvantHybrid::getImageRecords($itemId);
            if ($hybridImageRecords)
            {
                $hybrid = $hybridImageRecords[0];
                if ($thumbnail)
                {
                    $url = AvantHybrid::getThumbUrl($hybrid);
                }
                else
                {
                    $url = AvantHybrid::getImageUrl($hybrid);
                }
            }
        }
        return $url;
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
            // Use the item's image unless its empty, in which case use the cover image even though
            // the request was not to use it; however, better to show the cover image than nothing.
            $url = !empty($itemImageUrl) ? $itemImageUrl : $coverImageUrl;
        }

        if (empty($url))
            $url = self::getHybridItemImageUrl($item->id, $thumbnail);

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

        if (empty($url))
            $url = self::getHybridItemImageUrl($item->id, $thumbnail);

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