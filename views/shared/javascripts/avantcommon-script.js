function constructLightboxCaption(item)
{
    let viewPdfLink = ''
    let pdfUrl = item.el.attr('data-pdf');
    if (pdfUrl)
    {
        let rawPdfFileName = pdfUrl.substring(pdfUrl.lastIndexOf('/') + 1);
        let pdfFileName = rawPdfFileName.replace(/_/g, ' ');
        viewPdfLink = '<a class="lightbox-link" title="View PDF file ' +  pdfFileName + '" href="' + pdfUrl + '" target="_blank">' + 'View PDF' + '</a>';
    }

    let isForeignItem = item.el.attr('data-foreign') === '1';
    let target = isForeignItem ? ' target="_blank"' : '';

    let itemUrl = item.el.attr('data-itemUrl');
    let imageUrl = item.el.attr('href');
    let imageLink = '';
    let viewItemLink = ''
    let rawFileName = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);
    let fileName = rawFileName.replace(/_/g, ' ');
    imageLink = '<a class="lightbox-image-link" href="' + imageUrl + '" target="_blank" title="View this image in a separate window">' + fileName + '</a>';
    viewItemLink = '<a class="lightbox-link" title="View the metadata for this item" href="' + itemUrl + '"' + target + '>' + ITEM_LINK_TEXT + '</a>';

    let requestLink = '';
    let requestImageUrl = REQUEST_IMAGE_URL;
    let itemNumber = item.el.attr('data-itemNumber');
    if (!pdfUrl && requestImageUrl.length >  0 && !isForeignItem)
    {
        let itemId = item.el.attr('itemId');
        requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
        requestLink = '<a class="lightbox-link" href="' + requestImageUrl + ' " target="_blank" title="Make a request for this image">' + REQUEST_IMAGE_TEXT + '</a>';
    }

    let contributor = item.el.attr('data-contributor');
    let title = item.el.attr('data-title');
    let titleText = '<div class="mfp-caption-title">' + title + '</div>';
    if (contributor)
        titleText += '<div class="mfp-caption-contributor">' + contributor + '</div>';
    let caption = '<div>' + titleText + '<ul class="mfp-caption-fields">';

    caption += '<li class="mfp-caption-text-field mfp-caption-optional-field">Item #' + itemNumber + '</li>';
    caption += '<li class="mfp-caption-button-field">' + viewItemLink + '</li>';

    if (requestLink)
        caption += '<li class="mfp-caption-link-field ">' + requestLink + '</li>';

    if (pdfUrl)
        caption += '<li class="mfp-caption-button-field">' + viewPdfLink + '</li>';
    else
        caption += '<li class="mfp-caption-link-field mfp-caption-optional-field">' + imageLink + '</li>';

    caption += '<li class="mfp-caption-close"><a href="javascript:closeLightbox()" title="Close">&times;</a></li>';

    caption += '</ul>' + '</div>';
    return caption;
}

function closeLightbox()
{
    jQuery.magnificPopup.close();
}