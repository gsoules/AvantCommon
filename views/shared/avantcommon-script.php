<script type="text/javascript">
var REQUEST_IMAGE_URL = '<?php echo $requestImageUrl; ?>';
var REQUEST_IMAGE_TEXT = '<?php echo $requestImageText; ?>';
var ITEM_LINK_TEXT = '<?php echo $itemLinkText; ?>';

jQuery(document).ready(function()
{
    jQuery('.lightbox').magnificPopup(
    {
        type: 'image',
        showCloseBtn: false,
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