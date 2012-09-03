# Custom Meta Boxes

## Description

Custom Meta Boxes is a fork (non backwards compatible) of jaradatch's great https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress. 

## Why fork?

We really liked the idea of the original Custom Metaboxes, but were not so keen on the implementation. That's not to say it wasn't good, it's just not how we envisioned somethign like working from an API point of view.

This fork attemps to make the framework much more modular, taking a more Object Orientated approach. Though the original CMB does use Classes it's in a fairly basic (simiilar to a lot of the Classes in WordPress it's self). In this fork, each field type is an extension of the `abstract class` `CMB_Field`. This makes maintability and extension much easier and also makes writing tests a lot better.

The fork also contains a new field type called `CMB_Group_Field` which supports sub-fields. Group Fields are clonable (or _repeatable_), for example a meta box would have a `Group field` "Price", which would contain 2 sub fields "currency" and "amount". These can then be cloned as a pair, and saved in custom meta as such too.

This fork also simplifies the field types from the original. There is no "text", "text small", "text medium" as top level fields. There is only "text" which has a size property. This is the same for a lot of fields.

We have also added a basic layout engine for fields, any field has a `cols` argument. There is a maximum of 12 cols, so two fields of `6 cols` would give you a split down the middle (shown below).

![](https://dl.dropbox.com/u/238502/Captured/rjnI2.png)

### Field Types:
* text
* date picker
* date picker (unix timestamp)
* date time picker combo (unix timestamp)
* time picker
* color picker
* textarea
* select
* radio 
* radio inline
* taxonomy radio
* checkbox
* multicheck
* WYSIWYG/TinyMCE
* file upload
* DnD Image Upload Well

## Known Issues
* Image Upload Well does not work as repeatable fields

## To-do
* Make hooking / registering nicer. Perhaps use callbacks to reduce overhead of adding boxes when they will not be displayed.