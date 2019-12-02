# php-headless-chrome
Library to access headless chrome for php

## Overview

This library is for easy access to Headless Chrome on your php.

However, the library is just developing now.

## Useage

### Simple Screenshot
```php
$chrome = new Chrome();
$page = $chrome->getPage(0);
$page->moveTo('https://google.com')->captureTo('/tmp/capture.png');
```

