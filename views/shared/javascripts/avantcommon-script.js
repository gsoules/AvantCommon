function constructLightboxCaption(item)
{
    var title = item.el.attr('data-title');
    var itemId = item.el.attr('itemId');
    var itemNumber = item.el.attr('data-itemNumber');
    var isForeignItem = item.el.attr('data-foreign');
    var href = item.el.attr('href');
    var rawFileName = href.substring(href.lastIndexOf('/') + 1);
    var fileName = rawFileName.replace(/_/g, ' ');
    var requestImageUrl = REQUEST_IMAGE_URL;
    var itemUrl = item.el.attr('data-itemUrl');
    var titleText = '<div class="mfp-caption-title">' + title + '</div>';
    var target = isForeignItem === '1' ? ' target="_blank"' : '';
    var viewItemLink = '<a class="lightbox-link" title="Item #' + itemNumber + '" href="' + itemUrl + '"' + target + '>' + ITEM_LINK_TEXT + ' ' + itemNumber + '</a>';
    var imageLink = '';

    if (requestImageUrl.length >  0 && isForeignItem === '0')
    {
        requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
        imageLink = '<a class="lightbox-link" href="' + requestImageUrl + ' " target="_blank" title="Image ' + fileName + '">' + REQUEST_IMAGE_TEXT + '</a>';
    }
    else
    {
        imageLink = '<a class="lightbox-image-link" href="' + href + '" target="_blank" title="View image in a separate window">' + fileName + '</a>';
    }

    var caption = '<div>' + titleText + '<ul class="mfp-caption-links-container"><li class="mfp-caption-links">';
    caption += viewItemLink + '</li><li class="mfp-caption-links">' + imageLink;
    caption += '</li><li class="mfp-caption-close"><a href="javascript:closeLightbox()" title="Close">x</a></li></ul></div>';
    return caption;
}

function closeLightbox()
{
    jQuery.magnificPopup.close();
}