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
                var title = item.el.attr('title');
                var itemId = item.el.attr('itemId');
                var itemNumber = item.el.attr('data-itemNumber');
                var href = item.el.attr('href');
                var hasItemLink = itemId.length > 0;
                var rawFileName = href.substring(href.lastIndexOf('/') + 1);
                var fileName = rawFileName.replace(/_/g, ' ');
                var requestImageUrl = '<?php echo $requestImageUrl; ?>';
                var imageLink = '';
                if (requestImageUrl.length >  0)
                {
                    var requestImageText = '<?php echo $requestImageText; ?>';
                    requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
                    imageLink = '<a class="lightbox-link" href="' + requestImageUrl + ' "target="_blank">' + requestImageText + ' ' + fileName + '</a>';
                }
                else
                {
                    imageLink = '<a class="lightbox-image-link" href="' + href + '" target="_blank" title="View image in a separate window">' + fileName + '</a>';
                }
                var itemLink = '';
                var itemPath = '<?php echo $path; ?>'  + itemId;
                var itemLinkText = '<?php echo $itemLinkText; ?>';
                itemLink = '<div>' + title + '</div>';
                itemLink += '<span><a class="lightbox-link" href="' + itemPath + '">' + itemLinkText + ' #' + itemNumber + '</a></span>';
                var separator = '&nbsp;&nbsp;&#8212;&nbsp;&nbsp;';
                var caption = '<div>' + itemLink + separator +  imageLink + '</div>';
                return caption;
            }
        }
    }
    );
});
</script>