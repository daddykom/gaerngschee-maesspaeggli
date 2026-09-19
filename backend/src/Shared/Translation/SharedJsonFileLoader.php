<?php

declare(strict_types=1);

namespace App\Shared\Translation;

use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\MessageCatalogue;

final class SharedJsonFileLoader extends JsonFileLoader
{
    public function load($resource, $locale, $domain = 'messages'): MessageCatalogue
    {
        $catalogue = parent::load($resource, $locale, $domain);

        foreach ($catalogue->all() as $catalogueDomain => $messages) {
            foreach ($messages as $id => $message) {
                if (!is_string($message)) {
                    continue;
                }

                $catalogue->set(
                    $id,
                    preg_replace('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', '%$1%', $message) ?? $message,
                    $catalogueDomain,
                );
            }
        }

        return $catalogue;
    }
}
