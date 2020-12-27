<?php

class Data
{
    public function retrieve(): array
    {
        $raw = $this->fetch('https://ps5status.ru/api/data');
        $json = json_decode($raw, true);

        return $json['data']['shops'] ?? [];
    }

    private function fetch(string $url)
    {
        echo "Request $url\n";

        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.16; rv:84.0) Gecko/20100101 Firefox/84.0';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, 'https://ps5status.ru/');
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($responseCode === 200) {
            return $response;
        } else {
            throw new Exception("$url return %d", $responseCode);
        }
    }
}