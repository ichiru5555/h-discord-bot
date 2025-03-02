<?php

namespace Ichiru\DiscordBot;

use GuzzleHttp\Client;

class YouTube {

    /** @var string */
    private $channelId;

    public function __construct(string $channelId) {
        $this->channelId = $channelId;
    }

    /**
     * @return ?array<string>
     */
    public function checkNewVideo(): ?array {
        $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id=$this->channelId";
        $client = new Client();
        $response = $client->get($feedUrl);
        if ($response->getStatusCode() != 200) {
            return null;
        }
        $resbpdy = $response->getBody()->getContents();
        $xmlObject = simplexml_load_string($resbpdy);
        $json = json_decode(json_encode($xmlObject), true);
        $latestvideo = $json['entry'][0];
        $content['id'] = str_replace('yt:video:', '', $latestvideo['id']);
        $content['title'] = preg_replace('/#\S+/', '', $latestvideo['title']);
        $content['link'] = $latestvideo['link']['@attributes']['href'];
        return $content;
    }
}
