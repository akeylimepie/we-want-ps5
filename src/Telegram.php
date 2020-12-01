<?php

class Telegram
{
    private ?string $token;

    private ?int $channelId;

    public function __construct()
    {
        $settings = parse_ini_file(__DIR__.'/../telegram.ini');

        $this->token = $settings['token'] ?? null;
        $this->channelId = $settings['channel_id'] ?? null;
    }

    /**
     * @param $methodName
     * @param $data
     *
     * @return resource
     */
    private function buildRequest($methodName, $data = false)
    {
        $url = 'https://api.telegram.org';
        $url = sprintf('%s/bot%s/%s', $url, $this->token, $methodName);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        return $ch;
    }

    public function doRequest($methodName, $data = false)
    {
        $resource = $this->buildRequest($methodName, $data);
        $response = curl_exec($resource);

        return json_decode($response, true);
    }

    public function sendMessage(string $text)
    {
        return $this->doRequest(
            'sendMessage',
            [
                'chat_id' => $this->channelId,
                'text' => $text,
                'parse_mode' => 'markdown',
                'disable_web_page_preview' => true,
            ]
        );
    }
}