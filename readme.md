# HM Custom Meta Boxes for WordPress

Custom Meta Boxes is a framework for easily adding custom fields  to the WordPress post edit page.

It includes several field types including WYSIWYG, media upload and dates ([see wiki for a full list](https://github.com/humanmade/Custom-Meta-Boxes/wiki)). It also supports repeatable and grouped fields.

This project is aimed at developers and is easily extended and customised. It takes a highly modular, Object Orientated approach, with each field as an extension of the CMB_Field abstract class.

The framework also features a basic layout engine for fields, allowing you to align fields to a simple 12 column grid.

![Overview](https://f.cloud.github.com/assets/494927/386456/1ea0d6f6-a6a7-11e2-88ab-ce6497c2b757.png)

## Usage

* Include the custom-meta-boxes.php framework file. 
  * In your theme: include the CMB directory to your theme directory, and add `require_once( 'Custom-Meta-Boxes/custom-meta-boxes.php' );` to functions.php  
  * As an MU Plugin: [Refer to the WordPress Codex here for more information](http://codex.wordpress.org/Must_Use_Plugins)
* Filter `cmb_meta_boxes` to add your own meta boxes. [The wiki has more details and example code](https://github.com/humanmade/Custom-Meta-Boxes/wiki/Create-a-Meta-Box)

## Help

* For more information, including example code for usage of each field and instructions on how to create your own fields, refer to the [Wiki](https://github.com/humanmade/Custom-Meta-Boxes/wiki/).
* Not covered in the Wiki? Need more help? Get in touch. support@humanmade.co.uk
* Found a bug? Feature requests? [Create an issue - Thanks!](https://github.com/humanmade/Custom-Meta-Boxes/issues/new)

## About

This plugin is maintained by [Human Made Limited](http://hmn.md)

It began as a fork of [Custom Meta Boxes](https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress), but is no longer compatible.

## Known Issues
* Some fields do not work well as repeatable fields.
* Some fields do not work well in repeatable groups.

## To Do
* Make hooking / registering nicer.


## Contribution Guidelines ##

See [CONTRIBUTING.md](https://github.com/humanmade/Custom-Meta-Boxes/blob/master/CONTRIBUTING.md)

