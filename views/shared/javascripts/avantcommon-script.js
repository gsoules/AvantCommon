function constructLightboxCaption(item)
{
    let isForeignItem = item.el.attr('data-foreign') === '1';
    let contributor = item.el.attr('data-contributor');
    let title = item.el.attr('data-title');
    let itemId = item.el.attr('itemId');
    let itemNumber = item.el.attr('data-itemNumber');
    let pdfUrl = item.el.attr('data-pdf');
    let href = item.el.attr('href');
    let requestImageUrl = REQUEST_IMAGE_URL;
    let itemUrl = item.el.attr('data-itemUrl');
    let target = isForeignItem ? ' target="_blank"' : '';
    let imageLink = '';
    let pdfLink = '';
    let rawFileName = ''
    let viewItemLink = ''

    let titleText = '<div class="mfp-caption-title">' + title + '</div>';
    if (contributor)
        titleText += '<div class="mfp-caption-contributor">' + contributor + '</div>';

    if (pdfUrl)
    {
        let rawPdfFileName = pdfUrl.substring(pdfUrl.lastIndexOf('/') + 1);
        let pdfFileName = rawPdfFileName.replace(/_/g, ' ');
        pdfLink = '<a class="lightbox-image-link" href="' + pdfUrl + '" target="_blank" title="View this PDF file">' + pdfFileName + '</a>';
        viewPdfLink = '<a class="lightbox-link" title="View PDF file ' +  pdfFileName + '" href="' + pdfUrl + '" target="_blank">' + 'View PDF' + '</a>';
    }

    rawFileName = href.substring(href.lastIndexOf('/') + 1);
    let fileName = rawFileName.replace(/_/g, ' ');
    imageLink = '<a class="lightbox-image-link" href="' + href + '" target="_blank" title="View this image in a separate window">' + fileName + '</a>';
    viewItemLink = '<a class="lightbox-link" title="View the metadata for this item" href="' + itemUrl + '"' + target + '>' + ITEM_LINK_TEXT + '</a>';

    let requestLink = '';
    if (!pdfUrl && requestImageUrl.length >  0 && !isForeignItem)
    {
        requestImageUrl += '?id=' + itemId + '&item=' + itemNumber + '&file=' + rawFileName;
        requestLink = '<a class="lightbox-link" href="' + requestImageUrl + ' " target="_blank" title="Make a request for this image">' + REQUEST_IMAGE_TEXT + '</a>';
    }

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