# Purpose

Video streaming proxy using [nginx push stream module](https://github.com/wandenberg/nginx-push-stream-module) as stream broker.

# Usage:

`docker-compose up`

### publisher

Included publisher service is an proxy entry point.

### subscriber

Subscriber service subscribe itself in broker and sends streams to clients.

## Example:

Create streaming channel: http://video-publisher.docker/mp4?uri=http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4

Paste http://video-subscriber.docker/mpeg or http://video-subscriber.docker/mp4 in VLC to display video

## Extend
You may use external service to decode video, Add below to docker-compose file and use it together with publisher service to create channels: 

### ffmpeg

```yaml
ffmpeg-stream:
  image: jrottenberg/ffmpeg
  container_name: ffmpeg-stream
  expose:
    - 80
  command: -i http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4 -listen 1 -f mpeg http://0.0.0.0:80/ElephantsDream.mp4
```

Create channel: http://video-publisher.docker/mp4?uri=http://ffmpeg-stream/ElephantsDream.mp4

### vlc

```yaml
vlc-stream:
  build: ./input-modules/vlc
  container_name: vlc-stream
  expose:
    - 80
  command: http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4
```

Create channel: http://video-publisher.docker/mp4?uri=http://vlc-stream:8080
