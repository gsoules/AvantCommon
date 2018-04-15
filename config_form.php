<?php $view = get_view();

$identifierElementId = get_option('avantcommon_identifier');
if (empty($identifierElementId))
    $identifierElementName = 'Identifier';
else
    $identifierElementName = ItemMetadata::getElementNameFromId($identifierElementId);

$identifierAliasElementId = get_option('avantcommon_identifier_alias');
if (empty($identifierAliasElementId))
    $identifierAliasElementName = '';
else
    $identifierAliasElementName = ItemMetadata::getElementNameFromId($identifierAliasElementId);
?>

<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantCommon#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier"><?php echo __('Identifier Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The name of the element used to uniquely identify an Item."); ?></p>
        <?php echo $view->formText('avantcommon_identifier', $identifierElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier_alias"><?php echo __('Identifier Alias'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The name of the element used as an alias for the item Identifier. Leave blank if not using an alias."); ?></p>
        <?php echo $view->formText('avantcommon_identifier_alias', $identifierAliasElementName); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier_prefix"><?php echo __('Identifier Prefix'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Text that will appear before the identifier or alias."); ?></p>
        <?php echo $view->formText('avantcommon_identifier_prefix', get_option('avantcommon_identifier_prefix')); ?>
    </div>
</div>

