# AvantCommon

This plugin supports and is required by the following plugins:
 
* [AvantAdmin]
* [AvantCustom]
* [AvantElements]
* [AvantRelationships]
* [AvantSearch]
 
AvantCommon provides no functionality by itself, but provides common logic to support the plugins listed above.
See the **Usage** section below to learn how to configure this plugin.

## Dependencies
AvantCommon depends on the following open source libraries which are included in the `views/shared/javascripts` folder.
Click the links below to see copyrights and licenses for each.

* [Magnific Popup](https://github.com/dimsemenov/Magnific-Popup/) - lightbox for jQuery (only a dependency when using the Lightbox
feature described below).

## Installation

1. Unzip the AvantCommon-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantCommon.
1. Activate the plugin from the Admin → Settings → Plugins page.

### Lightbox feature

The AvantCommon plugin provides support for the Lightbox feature that is used by [AvantSearch]
and [AvantRelationships]
on pages that display thumbnails. When enabled, the Lightbox feature lets you click on a thumbnail to see it's
original-size image in a popup lightbox. You can then click the large image in the lightbox to see the next image on
the page (and continue clicking to see the others).

#### The Lightbox feature lets you:
* Click a thumbnail to see a larger image appear in the lightbox.
* See other images on the page by:
    * Clicking on the current lightbox image to see the next image
    * Clicking the left and right arrows to see the previous or next image
* Go to the item for the image being displayed in the lightbox by clicking on the image title link in the the bottom left corner.
If the title is the filename of an image or other file attached to the item being viewed, the title will have no hyperlink.
* Close the lightbox these three ways:
    * Click on the X in the upper right corner
    * Click anywhere on the page outside the lightbox
    * Press the Esc key

To enable the Lightbox feature, go to the AvantCommon configuration page and check the box for Enable Lightbox.
If you don't enable the Lightbox feature, clicking a thumbnail will cause the original image to open in a separate browser tab.

## Usage

AvantCommon has the following configuration options.

Option | Description
--------|------------
Identifier&nbsp;Element |  The element used to uniquely identify an Item. Often this is the Dublin Core `Identifier` element, but if your installation uses a different element for this purpose, specify it here. For example: `Object ID`. Note that the Identifier Element is what you'll using when establishing relationships using the [AvantRelationships] plugin.
Identifier Alias | The element used as an alias for the item Identifier. Leave this blank if you want the Identifier Element's value to display as an item's identifier. However, if your installation uses a different element for the public facing identifier value, specify that element here. For example, if you use a `Catalogue #` element to store composite catalogue number such as `2018.123.001`, you could specify that element here. For example: `Catalogue #` The alias will appear in search results and as the identifier for thumbnails.
Identifier Prefix | The prefix is text that will appear before the identifier or alias. You can leave it blank or provide a value. For example: `Item` or `Catalogue #`.
Private Elements | Elements that public users should not be able to see or search. Specify each element name on its on row. See additional information about this option below.
Unused&nbsp;Elements | Elements that your installation is not currently using and that should not appear on the admin Edit page. Specify each element name on its on row.
Start Year | The name of the element used to store a four digit year indicating the start of an item's date range.
End Year | The name of the element used to store a four digit year indicating the end of an item's date range.
Enable Lightbox | Check the box to display thumbnails using the Lightbox feature described above. This works with the thumbnails generated by [AvantSearch] and [AvantRelationships]. When this option is unchecked, clicking on a thumbnail displays the larger image in a separate browser tab.

#### Notes
* Start Year and End Year:
    * Start Year and End Year must both be provided or both left blank.
    * When Start Year and End Year are both provided, one of these two cases must be true:
        * The Start Year and End Year match the Date year. For exmaple, if the Date
         is `2018-06-18` then Start Year and End Year must both be `2018`.
        * The Date is blank and Start Year is less than End Year to indicate a date range.
    * Use the [AvantElements] **Validation Option** to validate Start Year and End year as years, and to validate the Date
element as a date.
* The Private Elements and Unused Elements options can be used together instead of the
 [Hide Elements](https://github.com/zerocrates/HideElements) plugin.

---
#### Private Elements Option
This option lets you specify a list of element names, one per row, that:
* Don't appear to public users on Show pages (they will appear to a logged in user -- see AvantTheme on how their labels
can be styled to indicate private)
* Don't appear to public users in the Fields dropdown on the Advanced Search page (they will appear to a logged in user)
* Should not be searchable via a keyword search

For example, you might have elements used to record internal information such as notes and item status that
contain information meant only for administrators. You can specify "Notes" and "Status" in the Private Elements text box to
prevent this information from being searched by the public.

Here are key points regarding private elements:

* Private elements will not appear as field selections on the Advanced Search page unless you are logged
in as an administrator.
* The text of private elements will not be recorded in the search_texts table, and therefore will not be searched when
performing a keyword search. This is true whether or not you are logged in as an administrator.
* To search for text in private elements, an administrator can do a field search in those fields, either through the public
Advanced Search page or using the native Omeka Advanced Search page.
* If you add an existing element to the private elements list, that element's text will still be contained in the
search_texts table and therefore be found via a keyword search. To hide the element's content, you
must reindex your Omeka database to force the search_texts table to be rebuilt without the private element text.
You do this by clicking the Index Records button on the Omeka
[Search Settings](https://omeka.org/classic/docs/Admin/Settings/Search_Settings/) page.
* If you uninstall AvantSearch and want to make private elements searchable again, reindex your Omeka database as
described in the previous bullet.
 
This features solves a problem in Omeka's native search whereby the text of all elements are searched, including
information that is hidden from public users by the [Hide Elements](http://omeka.org/classic/plugins/HideElements/)
plugin. This can produce keyword search results containing items that match the
search criteria, but that don't display the elements that resulted in the hit. For example, the search might
find keywords that appear in an item's hidden Notes element, but in no other public elements for that item. The user
then gets a search result that appears to contain none of the keywords they were looking for.

Below is an example specification of the Private Elements option.

```
Notes
Status
```

## Warning

Use this software at your own risk.

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

[AvantAdmin]:https://github.com/gsoules/AvantAdmin
[AvantCustom]:https://github.com/gsoules/AvantCustom
[AvantElements]:https://github.com/gsoules/AvantElements
[AvantRelationships]:https://github.com/gsoules/AvantRelationships
[AvantSearch]:https://github.com/gsoules/AvantSearch