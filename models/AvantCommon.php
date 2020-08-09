<?php

define ('UNTITLED_ITEM', __('Untitled'));
define ('PRIVATE_ITEM_PREFIX', __('* '));

class AvantCommon
{
    public static function elementHasPostedValue($elementId)
    {
        // Get the values from all of this element's input fields. Return true if any have a value.
        $values = $_POST['Elements'][$elementId];

        foreach ($values as $value)
        {
            if (strlen(trim($value['text'])) > 0)
            {
                return true;
            }
        }
        return false;
    }

    public static function emitAdminLinksHtml($itemId, $class, $newWindow, $suffix = '')
    {
        $html = '';
        $target = $newWindow ? ' target="_blank"' : '';
        $class .= ' ' . 'admin-links';

        $html .= "<div class='$class'>";
        $html .= '<a href="' . admin_url('/avant/show/' . $itemId) . '"' . $target . '>' . __('View') . '</a> ';
        $html .= ' | <a href="' . admin_url('/items/edit/' . $itemId) . '"' . $target . '>' . __('Edit') . '</a>';
        $html .= ' | <a href="' . admin_url('/avant/relationships/' . $itemId) . '"' . $target . '>' . __('Relationships') . '</a>';
        $html .= $suffix;
        $html .= '</div>';

        return $html;
    }

    public static function emitFlagItemAsRecent($itemId, $recentlyViewedItemIds)
    {
        if (in_array($itemId, $recentlyViewedItemIds))
        {
            $flagged = ' flagged';
            $tooltip = __('Remove from recently visited items list');
        }
        else
        {
            $flagged = '';
            $tooltip = __('Add to recently visited items list');
        }

        return "<span data-id='$itemId' class='recent-item-flag$flagged' title='$tooltip'>&nbsp;&nbsp;<a></a></span>";
    }

    public static function emitRecentlyViewedItems($recentlyViewedItems, $excludeItemId = '', $allowedItems = array(), $alreadyAddedItems = array())
    {
        $contextIsRelationshipsEditor = !empty($excludeItemId);

        $html = '';

        $count = count($recentlyViewedItems);

        // Deal with the case where the only recently viewed item is the excluded primary item.
        if ($count == 1 && $contextIsRelationshipsEditor)
        {
            $item = reset($recentlyViewedItems);
            if ($item->id == $excludeItemId)
            {
                $count = 0;
            }
        }

        $clearAll = $count == 0 ? '' : "<a id='recent-items-clear-all'>" . __('Clear all') . '</a>';

        $recentlyViewedItemInfo = array();
        foreach ($recentlyViewedItems as $recentlyViewedItem)
        {
            $identifier = ItemMetadata::getItemIdentifierAlias($recentlyViewedItem);
            $recentlyViewedItemInfo[$recentlyViewedItem->id] = array('item' => $recentlyViewedItem, 'identifier' => $identifier);
        }

        $findUrl = AvantCommon::getRecentlyViewedItemsSearchUrl($recentlyViewedItemInfo);
        $searchResultsLink = $count == 0 ? '' : "<a href='$findUrl' id='recent-items-as-search-results' target='_blank'>" . __('Show as search results') . '</a>';

        $html .= '<div id="recent-items-section">';
        $html .= '<div class="recent-items-title">';
        $html .= __('Recently Viewed Items') . ' (<span id="recent-items-count">' . $count . '</span>)';
        $html .= $searchResultsLink;
        $html .= $clearAll;
        $html .= '</div>';

        if ($count == 0)
        {
            $html .= '<div class="recent-items-message">' . __('Your recently viewed items list is empty.') . '</div>';
        }
        else
        {
            $html .= '<div id="recent-items">';

            foreach ($recentlyViewedItemInfo as $recentItemId => $info)
            {
                $recentItemIdentifier = $info['identifier'];
                if ($recentItemIdentifier == $excludeItemId)
                    continue;

                $recentItem = $info['item'];
                $itemPreview = new ItemPreview($recentItem);
                $thumbnail = $itemPreview->emitItemThumbnail(false);

                // Get the title as a link. If it's for the admin view, change to the public view.
                $title = $itemPreview->emitItemTitle(true);
                $title = str_replace('admin/items', 'items', $title);

                $type = ItemMetadata::getElementTextForElementName($recentItem, 'Type');
                if ($contextIsRelationshipsEditor)
                {
                    $type = "<span class='recent-item-type-emphasis'>$type</span>";
                    $subject = ItemMetadata::getElementTextForElementName($recentItem, 'Subject');
                    $metadata = "<div class='recent-item-metadata'><span>Type:</span>$type&nbsp;&nbsp;&nbsp;&nbsp;<span>Subject:</span>$subject</div>";
                }
                else
                {
                    $metadata = "<div class='recent-item-metadata'>$type</div>";
                }

                $removeTooltip = __('Remove item from this list (does not delete the item)');
                $removeLink = "<a class='recent-item-remove' data-id='$recentItemId' title='$removeTooltip'>" . __('Remove') . '</a>';

                $html .= "<div id='row-$recentItemId' class='recent-item-row'>";
                $html .= "<div class='recent-item-thumbnail' data-identifier='$recentItemIdentifier'>$thumbnail</div>";
                $html .= "<div class='recent-item'>";

                $addButton = '';
                if ($contextIsRelationshipsEditor && array_key_exists($recentItemId, $allowedItems))
                {
                    $disabled = in_array($recentItemId, $alreadyAddedItems) ? 'disabled' : '';
                    $addButton = "<button type='button' class='action-button recent-item-add' data-identifier='$recentItemIdentifier' $disabled>" . __('Add') . "</button>";
                }

                $html .= "<div class='recent-item-identifier' data-identifier='$recentItemIdentifier'><span>" . __('Item ') . "</span>$recentItemIdentifier$metadata</div>";

                $html .= "<div class='recent-item-title'>$addButton$title</div>";

                if (AvantCommon::userIsAdmin())
                {
                    $html .= AvantCommon::emitAdminLinksHtml($recentItemId, '', !$contextIsRelationshipsEditor, ' | ' . $removeLink);
                }
                else
                {
                    $html .= '<div class="admin-links">' . $removeLink . '</div>';
                }
                $html .= '</div>'; // recent-item

                $html .= '</div>'; // recent-item-row
            }

            $html .= '</div>'; // recent-items
        }
        $html .= '</div>'; // recent-items-section

        return $html;
    }

    public static function emitS3Link($identifier)
    {
        $bucket = S3Config::getOptionValueForBucket();
        $console = S3Config::getOptionValueForConsole();
        $path = S3Config::getOptionValueForPath();
        $region = S3Config::getOptionValueForRegion();
        $id = intval($identifier);
        $folder = $id - ($id % 1000);
        $link = "<a href='$console/$bucket/$path/$folder/$identifier/?region=$region&tab=overview' class='cloud-storage-link' target='_blank'>S3</a>";
        return $link;
    }

    public static function escapeQuotes($text)
    {
        // Returns a string with backslashes added before characters that need to be escaped.
        // The characters are: single quote ('), double quote ("), backslash (\), NUL (the NUL byte).
        // Use this method for string that will get inserted into SQL queries so that quotes within
        // the string don't create syntax errors in the query.
        return addslashes($text);
    }

    public static function fetchItemForRemoteRequest($itemId)
    {
        // This method fetches an item directly from the DB instead of using the normal Omeka methods because
        // those will return null if the item is not public and no user is logged in. Only use this method to
        // support remote requests where the logic needs to access public and non-public items, but because
        // the request is remote, there is no logged-in user.
        try
        {
            $db = get_db();
            $select = $db->select()->from($db->Items)->where('id = ?', $itemId);
            $item = $db->getTable('Item')->fetchObject($select);
        }
        catch (Exception $e)
        {
            $item = null;
        }
        return $item;
    }

    public static function getPostedValues($elementId)
    {
        $texts = array();

        if (!isset($_POST['Elements'][$elementId]))
        {
            $texts = array('');
        }
        else
        {
            $values = $_POST['Elements'][$elementId];

            foreach ($values as $value)
            {
                $texts[] = $value['text'];
            }
        }

        return $texts;
    }

    public static function getPostTextForElementName($elementName)
    {
        // Return the element's posted value. If it has more than one, only return the first.
        $text = '';
        $elementId = ItemMetadata::getElementIdForElementName($elementName);

        if (!empty($elementId))
        {
            // Use current() instead of [0] in case the 0th element was deleted using the Remove button.
            $values = $_POST['Elements'][$elementId];
            $text = empty($values) ? '' : current($values)['text'];
        }
        return $text;
    }

    public static function getRecentlySelectedRelationships()
    {
        $cookieValue = isset($_COOKIE['RELATIONSHIPS']) ? $_COOKIE['RELATIONSHIPS'] : '';
        $recentRelationshipCodes = empty($cookieValue) ? array() : explode(',', $cookieValue);

        $recentlySelectedRelationships = array();

        foreach ($recentRelationshipCodes as $recentCode)
        {
            $recentlySelectedRelationships[] = $recentCode;
        }

        return $recentlySelectedRelationships;
    }

    public static function getRecentlyViewedItemIds()
    {
        $cookieValue = isset($_COOKIE['RECENT']) ? $_COOKIE['RECENT'] : '';
        $recentItemIds = empty($cookieValue) ? array() : explode(',', $cookieValue);

        $ids = array();

        foreach ($recentItemIds as $recentItemId)
        {
            if (intval($recentItemId) == 0)
            {
                // This should never happen, but check in case the cookie is somehow corrupted.
                continue;
            }
            $ids[] = $recentItemId;
        }

        return $ids;
    }

    public static function getRecentlyViewedItems($excludeItemId = 0)
    {
        $recentlyViewedItemIds = AvantCommon::getRecentlyViewedItemIds();
        $deletedItemIds = array();

        $recentlyViewedItems = array();
        foreach ($recentlyViewedItemIds as $id)
        {
            if ($id == $excludeItemId)
                continue;

            // Get the item from its Id.
            $item = ItemMetadata::getItemFromId($id);
            if ($item)
            {
                $recentlyViewedItems[$id] = $item;
            }
            else
            {
                // The item does not exist - it must have been deleted since being recently viewed.
                $deletedItemIds[] = $id;
            }
        }

        // Emit a Javascript array of deleted Ids so the client-side code can remove them from the recent items cookie.
        if (count($deletedItemIds) > 0)
        {
            echo '<script>';
            echo 'var deletedRecentItemIds = [];';
            foreach ($deletedItemIds as $id)
            {
                echo "deletedRecentItemIds.push('$id');";
            }
            echo '</script>';
        }

        return $recentlyViewedItems;
    }

    public static function getRecentlyViewedItemsSearchUrl($recentlyViewedItemInfo)
    {
        $identifierList = '';
        foreach ($recentlyViewedItemInfo as $info)
        {
            if (!empty($identifierList))
                $identifierList .= '|';
            $identifierList .= $info['identifier'];
        }
        $identifierElementId = ItemMetadata::getElementIdForElementName(ItemMetadata::getIdentifierAliasElementName());
        $findUrl = ItemSearch::getAdvancedSearchUrl($identifierElementId, $identifierList, 'contains');

        // Limit the search to the local site since recent items are only tracked for the local site.
        $findUrl .= '&site=0';

        return $findUrl;
    }

    public static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public static function queryStringArg($arg, $defaultValue = '')
    {
        $value = isset($_GET[$arg]) ? $_GET[$arg] : $defaultValue;

        // If the default value is an integer, assume that the return value should also be an integer.
        return is_int($defaultValue) ? intval($value) : $value;
    }

    public static function queryStringArgOrCookie($arg, $cookie, $defaultValue = '')
    {
        $value = self::queryStringArg($arg);

        if (strlen(trim($value)) == 0)
        {
            // There is no value on the query string so see if there is a cookie value.
            $value = isset($_COOKIE[$cookie]) ? intval($_COOKIE[$cookie]) : $defaultValue;
        }

        // If the default value is an integer, assume that the return value should also be an integer.
        return is_int($defaultValue) ? intval($value) : $value;
    }

    public static function isSearchRequest()
    {
        $isQuery = isset($_GET['query']);
        $isKeywords = isset($_GET['keywords']);
        $isAdvanced = isset($_GET['advanced']);
        return $isQuery || $isKeywords || $isAdvanced;
    }

    public static function requestRemoteAsset($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE );
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $response = array();
        $response['response-code'] = $responseCode;
        $response['result'] = $result;
        $response['content-type'] = $contentType;

        return $response;
    }

    public static function sendEmailToAdministrator($title, $subject, $body)
    {
        $contributorId = option('avantelasticsearch_es_contributor_id');
        $user = current_user();
        $toEmail = get_option('administrator_email');
        $fromEmail = $user ? $user->email : $toEmail;
        $userName = $user ? $user->name : 'anonymous';
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($body);
        $mail->setFrom($fromEmail, "$title: $contributorId ($userName)");
        $mail->addTo($toEmail);
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        try
        {
            $mail->send();
            return true;
        }
        catch (Zend_Mail_Transport_Exception $e)
        {
            return false;
        }
    }

    public static function supportedImageMimeTypes()
    {
        return array(
            'image/jpg',
            'image/jpeg',
            'image/png'
        );
    }

    public static function userClickedSaveChanges()
    {
        // Determine if the admin clicked the Save Changes button. This check is done to distinguish from the cases
        // where an item is saved as part of another operation such as a batch edit or a reindex of search records.
        // When the user clicks the Save Changes button, the page posts to submit the Edit form to the server. The
        // other cases are usually peformed as part of a server-side background job that operates on multiple items.
        return isset($_POST['submit']) && ($_POST['submit'] == 'Save Changes' || $_POST['submit'] == 'Add Item');
    }

    public static function userIsAdmin()
    {
        $user = current_user();

        if (empty($user))
            return false;

        if ($user->role == 'researcher')
            return false;

        return true;
    }

    public static function userIsSuper()
    {
        $user = current_user();
        return ($user && $user->role == 'super');
    }
}