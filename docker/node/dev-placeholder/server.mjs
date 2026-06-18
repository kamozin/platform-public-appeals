import http from 'node:http';

const host = process.env.HOST || '0.0.0.0';
const port = Number(process.env.PORT || 3000);

const server = http.createServer((request, response) => {
  response.setHeader('content-type', 'text/html; charset=utf-8');
  response.end(`<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruka Dobra frontend placeholder</title>
    <meta name="robots" content="noindex,nofollow">
  </head>
  <body>
    <main>
      <h1>Nuxt SSR placeholder</h1>
      <p>Frontend application will be initialized in the next task.</p>
      <p>Requested path: ${request.url}</p>
    </main>
  </body>
</html>`);
});

server.listen(port, host, () => {
  console.log(`Frontend placeholder listening on http://${host}:${port}`);
});

