# AvantCommon

This plugin supports and is required by the following plugins:
 
* [AvantAdmin](https://github.com/gsoules/AvantAdmin)
* [AvantCustom](https://github.com/gsoules/AvantCustom)
* [AvantElements](https://github.com/gsoules/AvantElements)
* [AvantRelationships](https://github.com/gsoules/AvantRelationships)
* [AvantSearch](https://github.com/gsoules/AvantSearch)
 
AvantCommon provides no functionality by itself, but does require configuration to support the plugins above
that depend on it. See the **Usage** section below to learn how to configure this plugin.

## Dependencies
AvantCommon depends on the following open source libraries which are included in the `views/shared/javascripts` folder.
Click the links below to see copyrights and licenses for each.

* [Magnific Popup](https://github.com/dimsemenov/Magnific-Popup/) - lightbox for jQuery (only a dependency when using the Lightbox
feature described below).

## Installation

1. Unzip the AvantCommon-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantCommon.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. Enable the Lightbox feature (optional) as explained below.

#### Enabling the Lightbox feature

The AvantCommon plugin provides support for the Lightbox feature that is used by AvantSearch and AvantRelationships.
When enabled, the Lightbox feature lets you click on a thumbnail to see it's original-size image appear in a popup. You
can then click the large image in the popup to see the next image on the page (and continue clicking to see the others).
You can also use the popup's left and right arrows to set the other imageson the page. You can close the popup by
 licking on the X in the upper right corner of the popup, or by clicking on the page outside the popup, or by using the Esc key.

To enable the Lightbox feature, add the following line of code just above the closing `</body>` tag in your theme's `footer.php` file.

```
<?php echo $this->partial('/avantcommon-script.php'); ?>
```

 If you don't enable the Lightbox feature, clicking a thumbnail will cause the original image to open in a separate browser tab.

## Usage

AvantCommon has the following configuration options. The values of these options will determine how item previews are displayed.
An item preview is the item's thumbnail image with it's identifier above and it's title below.

Option | Description
--------|------------
Identifier Element |  The element used to uniquely identify an Item. Often this is the Dublin Core 'Identifier' element, but if your installation uses a different element for this purpose, specify it here. For example: `Item Type Metadata, Object ID`. Note that the Identifier Element is what you'll using when establishing relationships using the AvantRelationships plugin.
Identifier Alias | The element used as an alias for the item Identifier. Leave this blank if you want the Identifier Element's value to display as an item's identifier. However, if your installation uses a different element for the public facing identifier value, specify that element here. For example, if you use a `Catalogue #` element to store composite catalogue number such as `2018.123.001`, you could specify that element here. For example: `Item Type Metadata, Catalogue #`
Identifier Prefix | The prefix is text that will appear before the identifier or alias. You can leave it blank or provide a value. For example: `Item` or `Catalogue #'.
Title Element | The element used for an Item's title. Like the Identifier Element, specify an element here if you use another element besides the Dublin Core Title element to store an item's title information.

## Warning

Use it at your own risk.

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Created by [gsoules](https://github.com/gsoules) for the Southwest Harbor Public Library's [Digital Archive](http://swhplibrary.net/archive)
* Copyright George Soules, 2016-2017.
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information.

