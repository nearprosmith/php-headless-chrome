<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class Input extends DevToolsProtocol
{
    const insertText = 'Input.insertText';

    const dispatchKeyEvent = 'Input.dispatchKeyEvent';

    const KEY_MODIFIERS_ALT = 1;

    const KEY_MODIFIERS_CTRL = 2;

    const KEY_MODIFIERS_COMMAND = 4;

    const KEY_MODIFIERS_META = 4;

    const KEY_MODIFIERS_SHIFT = 8;

    public static function insertTextRequest(string $text)
    {
        return compact('text');
    }

    public static function dispatchKeyEventRequest(string $type, int $modifiers = null, float $timestamp = null, string $text = null, string $unmodifiedText = null, string $keyIdentifier = null, string $code = null, string $key = null, int $windowsVirtualKeyCode = null, int $nativeVirtualKeyCode = null, bool $autoRepeat = null, bool $isKeypad = null, bool $isSystemKey = null, int $location = null)
    {
        return DevToolsProtocol::buildRequest(compact('type','modifiers','timestamp','text','unmodifiedText','keyIdentifier','code','key','windowsVirtualKeyCode','nativeVirtualKeyCode','autoRepeat','isKeypad','isSystemKey','location'));
    }
}
