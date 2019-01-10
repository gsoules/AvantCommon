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
                    var href = item.el.attr('href');
                    var hasItemLink = itemId.length > 0;
                    var fileName = href.substring(href.lastIndexOf('/') + 1);
                    fileName = fileName.replace(/_/g, ' ');
                    var imageLink = '<div><a class="lightbox-image-link" href="' + href + '" target="_blank" title="View image in a separate window">' + fileName + '</a></div>';
                    var itemLink = '';
                    if (hasItemLink)
                    {
                        var itemPath = '<?php echo $path; ?>'  + itemId;
                        itemLink = '<div>' + title + '</div>';
                        itemLink += '<div><span><a class="lightbox-link" href="' + itemPath + '" title="View item"><?php echo $itemLinkText; ?></a></span></div>';
                    }
                    caption = itemLink + imageLink;
                    return caption;
                }
            }
        }
    );
});
</script>