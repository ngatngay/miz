#!/usr/bin/env bash

set -e

TARGET="$1"
URL="$2"

if [[ -z "$TARGET" || -z "$URL" ]]; then
    echo "Usage: $0 [target] [url]"
    exit 1
fi

mkdir -p "$(dirname "$TARGET")"

echo "Downloading $URL -> $TARGET"

curl -L --fail --output "$TARGET" "$URL"

