<?php
$view = get_view();

$identifierElementName = CommonConfig::getOptionTextForIdentifier();
$identifierAliasElementName = CommonConfig::getOptionTextForIdentifierAlias();
$identifierPrefix = CommonConfig::getOptionTextForIdentifierPrefix();
$startEndYearsOption = ElementsConfig::getOptionTextForStartEndYears();
?>

<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantCommon#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Identifier Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The name of the element used to uniquely identify an Item."); ?></p>
        <?php echo $view->formText(CommonConfig::OPTION_IDENTIFIER, $identifierElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Identifier Alias'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The name of the element used as an alias for the item Identifier. Leave blank if not using an alias."); ?></p>
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
        <label><?php echo __('Start/End Years'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The names of elements used to store Start and End years."); ?></p>
        <?php echo $view->formTextarea(CommonConfig::OPTION_START_END_YEARS, $startEndYearsOption, array('rows' => 2)); ?>
    </div>
</div>

