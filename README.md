koken-semantic-images
=====================

Adds semantic information to images displayed by Koken using shema.org based microformats.

##Usage
###Install
Download or clone this repository. Copy everything to:
 ```
storage/plugins/pulponair-semantic-images/
```
###Configure the plugin
Login to your koken installation and switch to settings->plugin. You should see a new plugin entry called "Semantic Images". If not you might need to clear the "system caches" and or reload the koken admin interface.

Next click on setup and configure the plugins behavior. If finished: enabled the plugin

###Check output

Surft to a frontend page containing at least one image. Check the source code and look for:
```
...<span itemscope itemtype="http://schema.org/ImageObject">...
```

You might also want to check your site using: http://www.google.de/webmasters/tools/richsnippets or http://linter.structured-data.org/

Done :)
