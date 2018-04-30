<?php
$view = get_view();

$identifierElementName = CommonConfig::getOptionTextForIdentifier();
$identifierAliasElementName = CommonConfig::getOptionTextForIdentifierAlias();
$identifierPrefix = CommonConfig::getOptionTextForIdentifierPrefix();
$startYearOption = CommonConfig::getOptionTextForYearStart();
$endYearOption = CommonConfig::getOptionTextForYearEnd();
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
        <label><?php echo CONFIG_LABEL_YEAR_START; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to store the Start year."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_YEAR_START, $startYearOption); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_YEAR_END; ?></label>
</div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to store the End year."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_YEAR_END, $endYearOption); ?>
    </div>
</div>

