<?php
use Kemer\Stream;
use Slim\Http\Request;
use Slim\Http\Response;
use GuzzleHttp\Exception as GuzzleException;

include_once 'vendor/autoload.php';

$app = new \Slim\App(
    $container = new \Slim\Container(include 'config/container.config.php')
);

$app->get('/mpeg', function ($request, $response, $args) {
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    try {
        (new \GuzzleHttp\Client())->request("GET", "http://nginx-push-stream/stats?id={$id}");
    } catch (GuzzleException\ClientException $e) {
        return $response->withStatus($e->getCode());
    }
    $command = sprintf(
        'ffmpeg -i %s -f mpegts pipe:',
        "http://nginx-push-stream/sub/{$id}"
    );
    $content = new Stream\Pipe\ReadPipeStream($command);
    return $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withBody($content);
});

$app->get('/mp4', function ($request, $response, $args) {
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    try {
        (new \GuzzleHttp\Client())->request("GET", "http://nginx-push-stream/stats?id={$id}");
    } catch (GuzzleException\ClientException $e) {
        return $response->withStatus($e->getCode());
    }
    $command = sprintf(
        'ffmpeg -f mpegts -i %s -c copy -bsf:v h264_mp4toannexb -movflags empty_moov+frag_keyframe -bsf dump_extra -f mp4 pipe:',
        "http://nginx-push-stream/sub/{$id}"
    );
    $content = new Stream\Pipe\ReadPipeStream($command);
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->get('/direct', function ($request, $response, $args) {
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    $content = new Stream\HttpStream("http://nginx-push-stream/sub/{$id}");
    return $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withBody($content);
});

$app->get('/piped', function ($request, $response, $args) {
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    $source = new Stream\HttpStream("http://nginx-push-stream/sub/{$id}");
    $command = sprintf(
        'ffmpeg -i pipe: -f mpegts pipe:'
    );
    $content = new Stream\Pipe\PipeStream($source, $command);
    return $response
        ->withHeader('Content-Type', 'video/mp4')
        ->withBody($content);
});

$app->run();
