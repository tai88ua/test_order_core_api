<?php

namespace App\Service;

class ScraperService
{

    public function fetchHtml(string $url) : string
    {
        $body = '';


        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
            ]
        ]);

        $body =  @file_get_contents($url, false, $context);

        return $body;
    }
}