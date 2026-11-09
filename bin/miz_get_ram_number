#!/usr/bin/env bash

awk '/MemTotal/ {printf "%.0f\n", ($2/1024/1024)+0.5}' /proc/meminfo
