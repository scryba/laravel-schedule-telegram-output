<?php

namespace Scryba\LaravelScheduleTelegramOutput\Tests;

class MessageFormattingTest extends TestCase
{
    /** @test */
    public function it_formats_markdownv2_messages_correctly()
    {
        $output = "Failed: The debug mode was expected to be `false`, but actually was `true`";
        $escapeMarkdown = function($string) {
            $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
            foreach ($specialChars as $char) {
                $string = str_replace($char, '\\' . $char, $string);
            }
            $string = preg_replace('/\//', '\\', $string); // escape backslashes
            return $string;
        };
        $outputMd = $escapeMarkdown($output);
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