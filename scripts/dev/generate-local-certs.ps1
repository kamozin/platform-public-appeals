if (-not (Get-Command mkcert -ErrorAction SilentlyContinue)) {
    Write-Error "mkcert is not installed. Install it first: https://github.com/FiloSottile/mkcert"
    exit 1
}

mkcert -install
mkcert `
    -cert-file docker/nginx/certs/rukadobra.localhost.pem `
    -key-file docker/nginx/certs/rukadobra.localhost-key.pem `
    rukadobra.localhost mail.rukadobra.localhost

Write-Output "Local certificates generated in docker/nginx/certs"

