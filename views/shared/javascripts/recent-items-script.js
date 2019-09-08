const ITEMS_COOKIE = 'ITEMS';
const MAX_RECENT_ITEMS = 16;

function removeAllItemsFromCookie()
{
    Cookies.set(ITEMS_COOKIE, '', {expires: 14});
}

function addRecentlyVisitedItem(itemId)
{
    var oldItemIds = retrieveRecentItemIds();
    var newItemIds = '';
    if (oldItemIds.length === 0)
    {
        newItemIds = itemId;
    }
    else
    {
        // Put the new Id at index 0, and copy the old Ids after it.
        newItemIds = [itemId];
        var count = 1;

        for (id of oldItemIds)
        {
            if (itemId === id)
            {
                // The Id was already in the stack. Ignore it since it's now on the top.
                continue;
            }
            newItemIds.push(id);
            count += 1;

            // Only show the last dozen Ids.
            if (count >= MAX_RECENT_ITEMS)
                break;
        }
        newItemIds = newItemIds.join(',');
    }

    Cookies.set(ITEMS_COOKIE, newItemIds, {expires: 14});
}

function removeRecentlyVisitedItem(itemId)
{
    var oldItemIds = retrieveRecentItemIds();
    var newItemIds = [];
    for (id of oldItemIds)
    {
        if (itemId === id)
        {
            // Remove the item bu not adding it to the new list.
            continue;
        }
        newItemIds.push(id);
    }
    newItemIds = newItemIds.join(',');

    Cookies.set(ITEMS_COOKIE, newItemIds, {expires: 14});
}

function retrieveRecentItemIds()
{
    var value = Cookies.get(ITEMS_COOKIE);
    var itemIds = [];
    if (value !== undefined)
    {
        itemIds = value.split(',');
    }

    return itemIds;
}
