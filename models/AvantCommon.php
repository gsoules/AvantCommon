<?php

class AvantCommon
{
    public static function batchEditing()
    {
        // Determine if the user has pressed the Index Records button on the admin Settings > Search page
        // or if they are doing batch editing via the Omeka admin interface. Note that the Bulk Editor plugin
        // operates directly on the database and does not save via Omeka's normal item update logic. Thus it
        // is not side-affected by AvantElements
        $batchEditing =
            isset($_POST['submit_index_records']) ||
            isset($_POST['batch_edit_hash']) ||
            isset($_POST['submit-batch-edit']);

        return $batchEditing;
    }

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

    public static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public static function setPostTextForElementId($elementId, $text)
    {
        $_POST['Elements'][$elementId][0]['text'] = $text;
    }
}