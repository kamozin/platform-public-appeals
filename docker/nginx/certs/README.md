# Local TLS certificates

Certificates in this directory are local-only and must not be committed.

Generate them with `mkcert`:

```bash
mkcert -install
mkcert \
  -cert-file docker/nginx/certs/rukadobra.localhost.pem \
  -key-file docker/nginx/certs/rukadobra.localhost-key.pem \
  rukadobra.localhost mail.rukadobra.localhost
```

