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
                    var fileName = href.substring(href.lastIndexOf('/') + 1);
                    fileName = fileName.replace(/_/g, ' ');
                    var requestImageUrl = '<?php echo $requestImageUrl; ?>';
                    var imageLink = '';
                    if (requestImageUrl.length >  0)
                    {
                        var requestImageText = '<?php echo $requestImageText; ?>';
                        requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + fileName;
                        imageLink = '<a class="lightbox-link" href="' + requestImageUrl + ' "target="_blank">' + requestImageText + ' ' + fileName + '</a>';
                    }
                    else
                    {
                        imageLink = '<div><a class="lightbox-image-link" href="' + href + '" target="_blank" title="View image in a separate window">' + fileName + '</a></div>';
                    }
                    var itemLink = '';
                    var itemPath = '<?php echo $path; ?>'  + itemId;
                    var itemLinkText = '<?php echo $itemLinkText; ?>';
                    itemLink = '<div>' + title + '</div>';
                    itemLink += '<div><span><a class="lightbox-link" href="' + itemPath + '">' + itemLinkText + ' #' + itemNumber + '</a></span></div>';
                    var caption = itemLink + imageLink;
                    return caption;
                }
            }
        }
    );
});
</script>