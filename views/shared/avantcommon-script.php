<script type="text/javascript">
var REQUEST_IMAGE_TEXT = '<?php echo $requestImageText; ?>';
var ITEM_LINK_TEXT = '<?php echo $itemLinkText; ?>';

jQuery(document).ready(function()
{
    jQuery('.lightbox').magnificPopup(
    {
        type: 'image',
        gallery: {
            enabled: true
        },
        image: {
            titleSrc: function (item)
            {
                return constructLightboxCaption(item);
            }
        }
    }
    );
});
</script>