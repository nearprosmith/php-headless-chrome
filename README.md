# php-headless-chrome
Library to access headless chrome for php

## Overview

This library is for easy access to Headless Chrome on your php.

However, the library is just developing now.

## Useage

### Simple Screenshot
```php
$chrome = new Chrome('path to chrome');
$page = $chrome->getPage(0);
$page->moveTo('https://google.com')->captureTo('/tmp/capture.png');
```


## Chrome Installation (for CentOS 7)

```shell script
# vim /etc/yum.repos.d/google-chrome.repo
```
```
[google-chrome]
name=google-chrome
baseurl=http://dl.google.com/linux/chrome/rpm/stable/x86_64
enabled=1
gpgcheck=1
gpgkey=https://dl.google.com/linux/linux_signing_key.pub
```
```shell script
# yum install -y google-chrome
```

### Installation of Japanese fonts (for Japanese)
```shell script
# yum install -y ipa-gothic-fonts ipa-mincho-fonts ipa-pgothic-fonts ipa-pmincho-fonts
# fc-cache -fv
```

### Usage

```php
$chrome = new Chrome('google-chrome');
$page = $chrome->getPage(0);
$page->moveTo('https://google.com')->captureTo('/tmp/capture.png');
```
