<?php


namespace App\Helpers;

class ConsoleHyperlink
{
    /**
     * Generate an ANSI OSC 8 hyperlink string for the console.
     *
     * @param string $url The destination URL.
     * @param string|null $text The visible link text (defaults to the URL).
     * @return string The escaped string ready for output.
     */
    public static function render(string $url, ?string $text = null): string
    {
        $text = $text ?: $url;
        return "\033]8;;{$url}\033\\{$text}\033]8;;\033\\";
    }

    /**
     * Directly output the hyperlink using Laravel's line() method.
     *
     * @param \Illuminate\Console\Command $command The command instance.
     * @param string $url
     * @param string|null $text
     * @return void
     */
    public static function output($command, string $url, ?string $text = null): void
    {
        $command->line(static::render($url, $text));
    }
}
