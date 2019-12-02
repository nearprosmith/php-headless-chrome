<?php

declare(strict_types=1);

namespace HeadlessChrome\Tests;

use HeadlessChrome\Chrome;
use HeadlessChrome\DevToolsProtocol\Page;
use PHPUnit\Framework\TestCase;

class ChromeTest extends TestCase
{
    /**
     * @var Chrome $chrome
     */
    static $chrome;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$chrome = new Chrome('/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome');
        unlink('./capture.png');
        unlink('./capture2.jpg');
        unlink('./capture3.png');
    }

    public function testToLaunchChrome(): void
    {
        $this->assertTrue(self::$chrome->isRunning());
        $this->assertStringContainsString('ws://localhost', self::$chrome->getEndpoints()[0]->webSocketDebuggerUrl);
    }

    public function testToMovePage(): void
    {
        $page = self::$chrome->getPage(0);
        $page->moveTo('https://twitter.com/login');
        $this->assertEquals($page->title, 'Twitterにログイン');
        $this->assertEquals($page->url, 'https://twitter.com/login');
    }

    public function testToCapture(): void
    {
        $page = self::$chrome->getPage(0);
        $page->moveTo('https://twitter.com/login');
        $page->captureTo('./capture.png');
        $this->assertFileExists('./capture.png');
    }

    public function testToOptionalCapture(): void
    {
        $page = self::$chrome->getPage(0);
        $page->moveTo('https://google.com');
        $page->captureTo('./capture2.jpg', Page::CAPTURE_TYPE_JPEG, 80);
        $this->assertFileExists('./capture2.jpg');
    }

    public function testToLoginTwitter(): void
    {
        $page = self::$chrome->getPage(0);
        $page->moveTo('https://twitter.com/login');
        $page->type('form.signin input[name="session[username_or_email]"]','username');
        $page->type('form.signin input[name="session[password]"]','password');
        $page->submit('form.signin');
        $page->waitForLoading();
        $page->captureTo('./capture3.png');
        $this->assertFileExists('./capture3.png');

    }

    public function testToInputText(): void
    {
        $page = self::$chrome->getPage(0);
        $page->moveTo('https://google.com');
        $page->type('input[name=q]',"test")->submit('form[name=f]')->waitForLoading();
        $this->assertStringContainsString('test',$page->title);
    }

}
