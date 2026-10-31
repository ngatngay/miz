#!/usr/bin/env bash

set -e

NAME="$1"
URL="$2"

if [[ -z "$NAME" || -z "$URL" ]]; then
    echo "Usage: $0 [name] [url]"
    exit 1
fi

TARGET="$(mktemp)"
DEST="/usr/local/bin/$NAME"

echo "Downloading $URL -> $TARGET"

curl -L --fail --output "$TARGET" "$URL"

cp -f "$TARGET" "$DEST"
chmod 755 "$DEST"

rm -f "$TARGET"

echo "Installed -> $DEST"

