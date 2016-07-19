<?php
use Kemer\Stream;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use Slim\Http\Request;
use Slim\Http\Response;

include_once 'vendor/autoload.php';

$container = new \Slim\Container();

$content = new Stream\HttpStream("http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4");
$command = 'ffmpeg -re -i http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4 -f mpegts pipe:';
$content = new Stream\Pipe\ReadPipeStream($command);

// Create a client with a base URI
$client = new GuzzleHttp\Client(['base_uri' => 'http://nginx-push-stream.docker']);
$body = "abcde";
$i = 0;
while (!$content->eof()) {
    if (!($body = $content->read(10000))) {
        continue;
    }
    //$body = ".";
    $response = $client->request(
        'POST',
        '/pub?id=my_channel_1',
        ['body' => $body]
    );
    echo "\n".$i++.": ".$response->getStatusCode();
    if ($response->getStatusCode() != 200) {
        var_dump($response);die;
    }
}
