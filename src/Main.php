<?php

namespace Ichiru\DiscordBot;

require_once __DIR__.'/../vendor/autoload.php';

use Discord\Discord;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;
use Ichiru\DiscordBot\YouTube;
use Error;

$dotenv = Dotenv::createImmutable(__DIR__.'/../')->load();

$logger = new Logger(basename(__FILE__));
$logger->pushHandler(new StreamHandler('discord.log', Logger::WARNING));

$config = [
    'discord' => [
        'token' => $_ENV['DISCORD_TOKEN'] ?? throw new Error('DISCORD_TOKEN is not set'),
        'channel_id' => $_ENV['DISCORD_CHANNEL_ID'] ?? throw new Error('DISCORD_CHANNEL_ID is not set'),
    ],
    'youtube' => [
        'channel_id' => $_ENV['YOUTUBE_CHANNEL_ID'] ?? throw new Error('YOUTUBE_CHANNEL_ID is not set'),
    ],
];

$discord = new Discord([
    'token' => $config['discord']['token'],
    'logger' => $logger,
]);

$discord->on('init', function (Discord $discord) use ($config) {
    $channel = $discord->getChannel($config['discord']['channel_id']);
    $id = file_get_contents(__DIR__.'/../id.txt');
    $updater = new YouTube($config['youtube']['channel_id']);
    $discord->getLoop()->addPeriodicTimer(3600, function () use ($channel, $id, $updater) {
        $video = $updater->checkNewVideo();
        if ($video !== null && $video['id'] !== $id) {
            $message = "新しい YouTube 動画がアップロードされました！\n" .
                   "タイトル: ".$video['title']."\n" .
                   "リンク: ".$video['link'];
            $channel->sendMessage($message);
            file_put_contents(__DIR__.'/../id.txt', $video['id']);
        }
    });
});

$discord->run();
