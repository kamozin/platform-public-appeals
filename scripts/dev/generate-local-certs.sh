#!/usr/bin/env sh
set -eu

if ! command -v mkcert >/dev/null 2>&1; then
  echo "mkcert is not installed. Install it first: https://github.com/FiloSottile/mkcert"
  exit 1
fi

mkcert -install
mkcert \
  -cert-file docker/nginx/certs/rukadobra.localhost.pem \
  -key-file docker/nginx/certs/rukadobra.localhost-key.pem \
  rukadobra.localhost mail.rukadobra.localhost

echo "Local certificates generated in docker/nginx/certs"

