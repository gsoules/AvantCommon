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

// From https://codereview.stackexchange.com/questions/139327/tooltip-overlay-following-the-mouse-pointer
document.addEventListener('DOMContentLoaded', function() {
    var cssSheet = document.styleSheets[0];
    var hoverIndex = cssSheet.insertRule('[data-tooltip]:hover:after {}', cssSheet.cssRules.length);
    var cssHover = cssSheet.cssRules[hoverIndex];
    Array.from(document.querySelectorAll('[data-tooltip]')).forEach(function (item) {
        item.addEventListener('mousemove', function (e) {
            if (this.dataset.tooltip === '') {
                cssHover.style.display = 'none';
                return;
            }
            cssHover.style.display = 'block';
            cssHover.style.left = (e.clientX + 8) + 'px';
            cssHover.style.top = (e.clientY + 12) + 'px';
        });
    });
});
</script>