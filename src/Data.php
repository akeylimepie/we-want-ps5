<?php

class Data
{
    public function retrieve(): array
    {
        $baseURL = 'https://ps5findstatusfrontend.s3.eu-central-1.amazonaws.com';

        $htmlSource = $this->fetch("$baseURL/index.html");

        preg_match('/\/js\/app\.[\w]+\.js/', $htmlSource, $match);

        $jsSource = $this->fetch("$baseURL{$match[0]}");

        preg_match('/https:\/\/[\w.\/]+status\.json/', $jsSource, $match);

        $raw = $this->fetch($match[0]);

        return json_decode($raw, true);
    }

    public function filterByTitle(array $data, string $title)
    {
        $list = [];

        foreach ($data as $store) {
            if (strcasecmp($store['title'] ?? '', $title) === 0) {
                $list[$store['site']] = $store;
            }
        }

        return $list;
    }

    private function fetch(string $url)
    {
        echo "Request $url\n";

        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.16; rv:83.0) Gecko/20100101 Firefox/83.0';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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