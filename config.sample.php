<?php

# Full path of easyweb.
define('www_root', '/path/to/easyweb/');

# Full path of the website. All relative pathes are relative to this path.
define('website_root', $_SERVER['DOCUMENT_ROOT']);

# Relative path and filename of the website config.
define('config_location', '/path/to/config.xml');

# Relative path and filename of the website localizatoin XML.
define('locale_location', '/path/to/locale.xml');

# Relative path and filename of the website cache. Folder of the cache file should be writeable for everyone. Warning: not implemented in draft.
define('cache_location',  '/path/to/cache.tmp');

?>