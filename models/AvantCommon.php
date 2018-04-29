<?php

class AvantCommon
{
    public static function elementHasPostedValue($elementId)
    {
        return !empty($_POST['Elements'][$elementId][0]['text']);
    }

    public static function getPostTextForElementId($elementId)
    {
        return $_POST['Elements'][$elementId][0]['text'];
    }

    public static function getPostTextForElementName($elementName)
    {
        $elementId = ItemMetadata::getElementIdForElementName($elementName);
        return self::getPostTextForElementId($elementId);
    }

    public static function setPostTextForElementId($elementId, $text)
    {
        $_POST['Elements'][$elementId][0]['text'] = $text;
    }
}