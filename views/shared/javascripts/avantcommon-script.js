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
    var viewItemLink = '<a class="lightbox-link" title="View the metadata for this item" href="' + itemUrl + '"' + target + '>' + ITEM_LINK_TEXT + '</a>';
    var imageLink = '';

    imageLink = '<a class="lightbox-image-link" href="' + href + '" target="_blank" title="View this image in a separate window">' + fileName + '</a>';
    var requestLink = '';
    if (requestImageUrl.length >  0 && isForeignItem === '0')
    {
        requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
        requestLink = '<a class="lightbox-link" href="' + requestImageUrl + ' " target="_blank" title="Make a request for this image">' + REQUEST_IMAGE_TEXT + '</a>';
    }

    var caption = '<div>' + titleText + '<ul class="mfp-caption-fields">';

    caption += '<li class="mfp-caption-text-field mfp-caption-optional-field">Item #' + itemNumber + '</li>';

    caption += '<li class="mfp-caption-button-field">' + viewItemLink + '</li>';

    if (requestLink)
        caption += '<li class="mfp-caption-link-field ">' + requestLink + '</li>';

    caption += '<li class="mfp-caption-link-field mfp-caption-optional-field">' + imageLink + '</li>';
    caption += '<li class="mfp-caption-close"><a href="javascript:closeLightbox()" title="Close">&times;</a></li>';

    caption += '</ul>' + '</div>';
    return caption;
}

function closeLightbox()
{
    jQuery.magnificPopup.close();
}