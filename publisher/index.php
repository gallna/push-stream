<?php
use Kemer\Stream;
use Slim\Http\Request;
use Slim\Http\Response;

include_once 'vendor/autoload.php';

$app = new \Slim\App(
    $container = new \Slim\Container(include 'config/container.config.php')
);

$app->get('/mp4', function ($request, $response, $args) use ($app) {
    if (false === ($uri = $request->getParam("uri", false))) {
        return $response->withStatus(400);
    }
    try {
        $content = new Stream\HttpStream($uri);
    } catch (\InvalidArgumentException $e) {
        return $response->withStatus(400);
    }

    $client = new GuzzleHttp\Client(['base_uri' => 'http://nginx-push-stream']);

    $app->respond($response->withStatus(201));
    fastcgi_finish_request();

    while (!$content->eof()) {
        if (!($body = $content->read(10000))) {
            continue;
        }
        $response = $client->request(
            'POST',
            '/pub?id=my_channel_1',
            ['body' => $body]
        );
        if ($response->getStatusCode() != 200) {
            error_log(sprintf(
                "nginx-push-stream server response %s - %s",
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            die;
        }
    }
});

$app->get('/mpeg', function ($request, $response, $args) use ($app) {
    if (false === ($uri = $request->getParam("uri", false))) {
        return $response->withStatus(400);
    }
    $command = sprintf('ffmpeg -re -i %s -f mpegts pipe:', $uri);
    $content = new Stream\Pipe\ReadPipeStream($command);
    $client = new GuzzleHttp\Client(['base_uri' => 'http://nginx-push-stream']);

    $app->respond($response->withStatus(201));
    fastcgi_finish_request();

    while (!$content->eof()) {
        if (!($body = $content->read(10000))) {
            continue;
        }
        $response = $client->request(
            'POST',
            '/pub?id=my_channel_1',
            ['body' => $body]
        );
        if ($response->getStatusCode() != 200) {
            error_log(sprintf(
                "nginx-push-stream server response %s - %s",
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            die;
        }
    }
});

$app->run();
