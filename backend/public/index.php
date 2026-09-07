<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Configuration\Environment;
use App\Application;
use Slim\Factory\ServerRequestCreatorFactory;

Environment::load();
$app = Application::create();
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$requestPath = $request->getUri()->getPath();
if ($requestPath === '/api' || str_starts_with($requestPath, '/api/')) {
    $routePath = substr($requestPath, 4) ?: '/';
    $request = $request->withUri($request->getUri()->withPath($routePath));
}

$response = $app->handle($request);

$response->getBody()->rewind();
header(
    sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    )
);

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
