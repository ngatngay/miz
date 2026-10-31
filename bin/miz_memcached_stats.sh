#!/usr/bin/env bash

echo stats | socat - UNIX-CONNECT:/run/memcached/memcached.sock
