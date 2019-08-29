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
        return isset($_GET['query']) || isset($GET['advanced']);
    }

    public static function setPostTextForElementId($elementId, $text)
    {
        $_POST['Elements'][$elementId][0]['text'] = $text;
    }

    public static function sendEmailToAdministrator($subject, $body)
    {
        $contributorId = option('avantelasticsearch_es_contributor_id');
        $user = current_user();
        $toEmail = get_option('administrator_email');
        $fromEmail = $user ? $user->email : $toEmail;
        $userName = $user ? $user->name : 'anonymous';
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($body);
        $mail->setFrom($fromEmail, "ES Error: $contributorId ($userName)");
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
}