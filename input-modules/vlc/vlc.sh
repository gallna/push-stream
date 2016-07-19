#!/bin/bash

URL=$1
PORT=${2:-80}

exec cvlc -vvv "$URL" --rate 1 --sout="#std{access=http,mux=ts,dst=:$PORT}" \
    vlc://quit
