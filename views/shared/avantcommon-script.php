<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery('.lightbox').magnificPopup(
    {
        type: 'image',
        gallery: {
            enabled: true
        },
        image: {
            titleSrc: function (item) {
                var title = item.el.attr('data-title');
                var itemId = item.el.attr('itemId');
                var itemNumber = item.el.attr('data-itemNumber');
                var isForeignItem = item.el.attr('data-foreign');
                var href = item.el.attr('href');
                var rawFileName = href.substring(href.lastIndexOf('/') + 1);
                var fileName = rawFileName.replace(/_/g, ' ');
                var requestImageUrl = '<?php echo $requestImageUrl; ?>';
                var imageLink = '';
                if (requestImageUrl.length >  0 && isForeignItem === '0')
                {
                    var requestImageText = '<?php echo $requestImageText; ?>';
                    requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
                    imageLink = '<a class="lightbox-link" href="' + requestImageUrl + ' " target="_blank" title="Image ' + fileName + '">' + requestImageText + '</a>';
                }
                else
                {
                    imageLink = '<a class="lightbox-image-link" href="' + href + '" target="_blank" title="View image in a separate window">' + fileName + '</a>';
                }
                var separator = '&nbsp;&nbsp;&#8212;&nbsp;&nbsp;';
                var itemUrl = item.el.attr('data-itemUrl');
                var itemLinkText = '<?php echo $itemLinkText; ?>';
                var titleText = '<div class="mfp-caption-title">' + title + '</div>';
                var viewItemLink = '<a class="lightbox-link" title="Item #' + itemNumber + '" href="' + itemUrl + '">' + itemLinkText + '</a>';
                var caption = '<div>' + titleText + '<div class="mfp-caption-links">' + viewItemLink + separator +  imageLink + '</div></div>';
                return caption;
            }
        }
    }
    );
});
</script>