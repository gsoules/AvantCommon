<?php $view = get_view();

$identifierElement = get_option('avantcommon_identifier');
if (empty($identifierElement))
    $identifierElement = 'Dublin Core, Identifier';

$titleElement = get_option('avantcommon_title');
if (empty($titleElement))
    $titleElement = 'Dublin Core, Title';

?>
<p>
    The values specified below must be in the form "&lt;element set name&gt;, &lt;element name&gt;".
    <br/>
    The two values must be separated by a comma with or without spaces around it.
    <br/>
    The element set name must be "Dublin Core" or "Item Type Metadata".
    <br/>
    Examples: "Dublin Core, Identifier" or "Item Type Metadata, Catalog Number".
</p>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier"><?php echo __('Identifier Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to uniquely identify an Item"); ?></p>
        <?php echo $view->formText('avantcommon_identifier', $identifierElement); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier_alias"><?php echo __('Identifier Alias'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used as an alias for the item Identifier"); ?></p>
        <?php echo $view->formText('avantcommon_identifier_alias', get_option('common_identifier_alias')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_identifier_prefix"><?php echo __('Identifier Prefix'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Text that will appear before the identifier or alias"); ?></p>
        <?php echo $view->formText('avantcommon_identifier_prefix', get_option('common_identifier_prefix')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantcommon_title"><?php echo __('Title Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used for an Item's title"); ?></p>
        <?php echo $view->formText('avantcommon_title', $titleElement); ?>
    </div>
</div>


