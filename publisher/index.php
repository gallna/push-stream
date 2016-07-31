<?php
use Kemer\Stream;
use Slim\Http\Request;
use Slim\Http\Response;

include_once 'vendor/autoload.php';

$publisher = $id = null;

use Psr\Http\Message\StreamInterface;
class Publisher
{
    private $id;
    private $client;
    private $stream;

    public function __construct($id, StreamInterface $stream = null)
    {
        $this->id = $id;
        $stream and $this->setStream($stream);
    }

    public function setStream(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function push(GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->pushStream($this->stream);
    }

    public function delete()
    {
        $this->client->request(
            'DELETE',
            "/pub?id={$this->id}"
        );
        error_log("channel deleted");
    }

    public function pushStream(StreamInterface $stream)
    {
        while (!$stream->eof()) {
            try {
                if (!($body = $stream->read(10000))) {
                    error_log("no more body");
                    break;
                }
            } catch (\Exception $e) {
                error_log(sprintf(
                    "stream exception %s - %s",
                    $e->getCode(),
                    $e->getMessage()
                ));
                break;
            }

            $response = $this->client->request(
                'POST',
                "/pub?id={$this->id}",
                ['body' => $body]
            );
            if ($response->getStatusCode() != 200) {
                $this->delete();
                error_log(sprintf(
                    "nginx-push-stream server response %s - %s",
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                ));
            }
        }
        error_log("stream finished");
        $this->delete();
    }
}



$app = new \Slim\App(
    $container = new \Slim\Container(include 'config/container.config.php')
);

$app->post('/mp4', function ($request, $response, $args) use ($app, &$publisher, &$id) {
    if (false === ($uri = $request->getParam("uri", false))) {
        return $response->withStatus(400);
    }
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    try {
        $stream = new Stream\HttpStream($uri);
    } catch (\InvalidArgumentException $e) {
        return $response->withStatus(400);
    }

    $publisher = new Publisher($id, $stream);
    return $response;
});

$app->post('/mpeg', function ($request, $response, $args) use ($app, &$publisher, &$id) {
    if (false === ($uri = $request->getParam("uri", false))) {
        return $response->withStatus(400);
    }
    if (false === ($id = $request->getParam("id", false))) {
        return $response->withStatus(400);
    }
    $command = sprintf('ffmpeg -re -i %s -f mpegts pipe:', $uri);
    $stream = new Stream\Pipe\ReadPipeStream($command);
    $publisher = new Publisher($id, $stream);
    return $response;
});

$app->run();

$client = new GuzzleHttp\Client(['base_uri' => 'http://nginx-push-stream']);

fastcgi_finish_request();
$publisher->push($client);
