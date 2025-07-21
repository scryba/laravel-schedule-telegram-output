<?php

namespace Scryba\LaravelScheduleTelegramOutput\Tests;

use Scryba\LaravelScheduleTelegramOutput\TelegramNotifier;

class MessageFormattingTest extends TestCase
{
    /** @test */
    public function it_formats_markdownv2_messages_correctly()
    {
        $output = "Failed: The debug mode was expected to be `false`, but actually was `true`";
        $outputMd = TelegramNotifier::escapeMarkdownV2($output);
        $contents = "*🤖 Scheduled Job Output*\n\n";
        $contents .= "*Output:*\n" . $outputMd;
        $this->assertStringContainsString('*🤖 Scheduled Job Output*', $contents);
        $this->assertStringContainsString('Failed:', $contents);
        $this->assertStringContainsString('debug mode', $contents);
    }

    /** @test */
    public function it_formats_html_messages_correctly()
    {
        $output = "Failed: The debug mode was expected to be false, but actually was true";
        $outputHtml = e($output);
        $outputPre = '<pre>' . $outputHtml . '</pre>';
        $contents = "<b>🤖 Scheduled Job Output</b><br><br>";
        $contents .= "<b>Output:</b><br>" . $outputPre;
        $this->assertStringContainsString('<b>🤖 Scheduled Job Output</b>', $contents);
        $this->assertStringContainsString('<pre>', $contents);
        $this->assertStringContainsString('debug mode', $contents);
    }
} 