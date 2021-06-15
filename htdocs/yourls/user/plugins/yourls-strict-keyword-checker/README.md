Strict keyword checker
======================

Plugin for [YOURLS](http://yourls.org) `1.7` (not tested for previous versions).

Description
-----------

When you have a public URL shortener that allows custom keywords, you have to make sure that some 'funny' people don't abuse the system by using swear words (or whatever you may not see, like names of a competitor or whatever). Normally, the default installation provides a list of words `$yourls_reserved_URL` to which you can add words. The problem with this list is however that it will check whether the word `%$#!` appears in the list, it will not check whether your custom keyword `%$#!OFF` _contains_ a word that's in the list.  This plugin does.

Installation
------------
1. In `/user/plugins`, create a new folder named `yourls-strict-keyword-checker`.
2. Drop these files in that directory.
3. Go to the Plugins administration page ( *eg* `http://sho.rt/admin/plugins.php` ) and activate the plugin.
4. Have fun!

License
-------
MIT
