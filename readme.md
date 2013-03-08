LessModule module for Venne:CMS
===============================

This module is official extension for Venne:CMS. Thank you for your interest.

Installation
------------

- Copy this folder to /vendor/venne
- Active this module in administration

Usage
-----

In latte templates:

	{less @fooModule/less/style.less}

On command line

	php www/index.php less:compile $fileIn $fileOut
