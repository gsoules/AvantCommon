<?php $view = get_view();

$identifierElement = get_option('common_identifier');
if (empty($identifierElement))
    $identifierElement = 'Dublin Core, Identifier';

$titleElement = get_option('common_title');
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
        <label for="common_identifier"><?php echo __('Identifier Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used to uniquely identify an Item"); ?></p>
        <?php echo $view->formText('common_identifier', $identifierElement); ?>
    </div>
</div>


<div class="field">
    <div class="two columns alpha">
        <label for="common_title"><?php echo __('Title Element'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The element used for an Item's title"); ?></p>
        <?php echo $view->formText('common_title', $titleElement); ?>
    </div>
</div>


