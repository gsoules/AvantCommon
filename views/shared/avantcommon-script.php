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
                    var id = item.el.attr('id');
                    if (id.length > 0)
                    {
                        var url = '<?php echo $path; ?>'  + id;
                        title = '<a class="lightbox-link" href="' + url + '">' + title + '</a>';
                    }
                    return title;
                }
            }
        }
    );
});
</script>