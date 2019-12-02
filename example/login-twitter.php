<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$chrome = new \HeadlessChrome\Chrome(null, 9222);
$page = $chrome->getPage(0);
$page->moveTo('https://twitter.com/login');
