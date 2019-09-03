<script type="text/javascript">
jQuery(document).ready(function ()
{
    var itemId = '<?php echo $itemId; ?>';
    const ITEMS_COOKIE = 'ITEMS';
    const MAX_RECENT_ITEMS = 16;

    function addActionRemoveListeners()
    {
        var removeX = jQuery('.recently-viewed-item-removed');

        removeX.click(function ()
        {
            var itemId = jQuery(this).attr('data-item-id');
            var itemToRemove = jQuery('#recent-' + itemId);
            itemToRemove.remove();
            removeItemFromCookie(itemId);
        });
    }

    function removeItemFromCookie(itemId)
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

    function saveRecentItemId()
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

    saveRecentItemId();
    addActionRemoveListeners();
});
</script>
