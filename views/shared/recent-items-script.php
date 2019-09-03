<script type="text/javascript">
jQuery(document).ready(function ()
{
    var itemId = '<?php echo $itemId; ?>';
    const ITEMS_COOKIE = 'ITEMS';

    function retrieveRecentItemIds()
    {
        var value = Cookies.get(ITEMS_COOKIE);
        var ids = [];
        if (value !== undefined)
        {
            ids = value.split(',');
        }

        return ids;
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
                if (count >= 12)
                    break;
            }
            newItemIds = newItemIds.join(',');
        }

        Cookies.set(ITEMS_COOKIE, newItemIds, {expires: 14});
    }

    saveRecentItemId();
});
</script>
