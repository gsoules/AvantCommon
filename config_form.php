<?php
$view = get_view();

$identifierElementName = CommonConfig::getOptionTextForIdentifier();
$identifierAliasElementName = CommonConfig::getOptionTextForIdentifierAlias();
$identifierPrefix = CommonConfig::getOptionTextForIdentifierPrefix();
$startYearOption = ElementsConfig::getOptionTextForYearStart();
$endYearOption = ElementsConfig::getOptionTextForYearEnd();
?>

<div class="plugin-help learn-more">
    <a href="https://github.com/gsoules/AvantCommon#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Identifier Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to uniquely identify an Item."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER, $identifierElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Identifier Alias'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used as an alias for the Item Identifier element."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER_ALIAS, $identifierAliasElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Identifier Prefix'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Text that will appear before the identifier or alias."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER_PREFIX, $identifierPrefix); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Start Year'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to store the Start year."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_YEAR_START, $startYearOption); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('End Year'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to store the End year."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_YEAR_END, $endYearOption); ?>
    </div>
</div>

