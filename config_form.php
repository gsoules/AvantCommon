<?php
$view = get_view();

$identifierElementName = CommonConfig::getOptionTextForIdentifier();
$identifierAliasElementName = CommonConfig::getOptionTextForIdentifierAlias();
$identifierPrefix = CommonConfig::getOptionTextForIdentifierPrefix();

$privateElementsOption = CommonConfig::getOptionTextForPrivateElements();
$privateElementOptionRows = max(2, count(explode(PHP_EOL, $privateElementsOption)));

$requestImageUrl = CommonConfig::getOptionTextForRequestImageUrl();

$unusedElementsOption = CommonConfig::getOptionTextForUnusedElements();
$unusedElementsOptionRows = max(2, count(explode(PHP_EOL, $unusedElementsOption)));
?>

<div class="plugin-help learn-more">
    <a href="https://github.com/gsoules/AvantCommon#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_IDENTIFIER; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to uniquely identify an Item."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER, $identifierElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_IDENTIFIER_ALIAS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used as an alias for the Item Identifier element."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER_ALIAS, $identifierAliasElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_IDENTIFIER_PREFIX; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Text that will appear before the identifier or alias."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER_PREFIX, $identifierPrefix); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_PRIVATE_ELEMENTS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should not be visible to public users."); ?></p>
        <?php echo $view->formTextarea(CommonConfig::OPTION_PRIVATE_ELEMENTS, $privateElementsOption, array('rows' => $privateElementOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_UNUSED_ELEMENTS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that are not being used by this installation."); ?></p>
        <?php echo $view->formTextarea(CommonConfig::OPTION_UNUSED_ELEMENTS, $unusedElementsOption, array('rows' => $unusedElementsOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_LIGHTBOX ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Enable the Lightbox feature for displaying images.'); ?></p>
        <?php echo $view->formCheckbox(CommonConfig::OPTION_LIGHTBOX, true, array('checked' => (boolean)get_option(CommonConfig::OPTION_LIGHTBOX))); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_REQUEST_IMAGE_URL; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The URL of the web page to display when a user requests an image."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_REQUEST_IMAGE_URL, $requestImageUrl); ?>
    </div>
</div>
