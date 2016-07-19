<?php
use Kemer\Stream;
use Slim\Http\Request;
use Slim\Http\Response;

include_once 'vendor/autoload.php';

$app = new \Slim\App(
    $container = new \Slim\Container(include 'config/container.config.php')
);

$app->get('/mpeg', function ($request, $response, $args) {
    $content = new Stream\HttpStream("http://nginx-push-stream/sub/my_channel_1.b20");
    return $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withBody($content);
});

$app->get('/mp4', function ($request, $response, $args) {
    $command = sprintf(
        'ffmpeg -f mpegts -i %s -c copy -bsf:v h264_mp4toannexb -movflags empty_moov+frag_keyframe -f mp4 pipe:',
        'http://nginx-push-stream/sub/my_channel_1.b20'
    );
    $content = new Stream\Pipe\ReadPipeStream($command);
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->get('/mp4-direct', function ($request, $response, $args) {
    $content = new Stream\HttpStream("http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4");
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->get('/mp4-decoded', function ($request, $response, $args) {
    $command = sprintf(
        'ffmpeg -i "%s" -f mpegts pipe:',
        "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4"
    );
    $content = new Stream\Pipe\ReadPipeStream($command);
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->get('/mp4-piped', function ($request, $response, $args) {
    $source = new Stream\HttpStream("http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4");
    $command = sprintf(
        'ffmpeg -i pipe: -f mpegts pipe:'
    );
    $content = new Stream\Pipe\PipeStream($source, $command);
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->run();
