<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\OfferRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Response;

final class OfferRoutes
{
    public static function register(App $app): void
    {
        $app->get('/api/start', function ($request, ResponseInterface $response) {
            $repository = new OfferRepository();
            $offers = $repository->findAll();
            $body = json_encode($offers, JSON_THROW_ON_ERROR);
            $response->getBody()->write($body);
            return $response
                ->withHeader('Content-Type', 'application/json');
        });
    }
}