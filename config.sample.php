<?php

# Full path of easyweb.
define('www_root', '/path/to/easyweb/');

# Full path of the website. All relative pathes are relative to this path.
define('website_root', $_SERVER['DOCUMENT_ROOT']);

# Relative path and filename of the website config.
define('config_location', '/path/to/config.xml');

# Relative path and filename of the website localizatoin XML.
define('locale_location', '/path/to/locale.xml');

# Relative path of the website cache folder. This folder should be writeable for everyone.
define('cache_location',  '/path/to/cache/');

?>