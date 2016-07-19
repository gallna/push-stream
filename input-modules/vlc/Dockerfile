FROM ubuntu:14.04

MAINTAINER Tomasz Jonik <tom.jonik@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV VLC_VERSION 2.1.6\*
RUN apt-get update; \
    apt-get install -y vlc=$VLC_VERSION; \
    rm -rf /var/lib/apt/lists/*

COPY vlc.sh /
EXPOSE 8080
RUN useradd -m vlc
USER vlc
ENTRYPOINT ["/vlc.sh"]
