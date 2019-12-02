<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$chrome = new \HeadlessChrome\Chrome('/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome');
$page = $chrome->getPage(0);
$page->moveTo('https://google.com')->captureTo('/tmp/capture.png');
