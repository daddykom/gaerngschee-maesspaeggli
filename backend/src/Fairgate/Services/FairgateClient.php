<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Ecdsa\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Psr\Http\Client\ClientInterface;

final class FairgateClient implements FairgateContactProvider
{
    private const CONTACTS_PATH = '/fsa/v1.1/contact/%s/contacts/list';
    private const CONTACT_DATA_PATH = '/fsa/v2.0/contact/%s/data/%s';
    private const TOKEN_PATH = '/fsa/v1.1/auth/create/%s/token';

    private ?string $bearerToken = null;

    /**
     * @param callable(string): bool|null $tokenValidator
     */
    public function __construct(
        private readonly ?ClientInterface $httpClient = null,
        private readonly ?string $baseUrl = null,
        private readonly ?string $organizationId = null,
        private readonly ?string $accessKey = null,
        private readonly ?string $publicKey = null,
        ?callable $tokenValidator = null,
    ) {
        $this->tokenValidator = $tokenValidator ?? $this->validateToken(...);
    }

    /** @var callable(string): bool */
    private $tokenValidator;

    public function hasContactByEmail(string $email): bool
    {
        $data = $this->findContactsByEmail($email);
        $contacts = $data['data']['contacts'] ?? [];

        foreach ($contacts as $contact) {
            $contactEmail = strtolower(trim((string) ($contact['communication']['primary_email'] ?? '')));
            if ($contactEmail === strtolower(trim($email))) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    public function findContactsByEmail(string $email): array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return ['success' => true, 'data' => ['contacts' => []]];
        }

        try {
            $response = $this->client()->request('GET', sprintf(self::CONTACTS_PATH, $this->organizationId()), [
                'headers' => $this->headers(),
                'query' => [
                    'primary_email' => $email,
                    'pageNo' => 1,
                    'pageLimit' => 1,
                ],
            ]);
        } catch (GuzzleException $exception) {
            throw new FairgateException('FSA contact request failed.', 0, $exception);
        }

        $data = $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());

        return $data;
    }

    /** @return array<string, mixed> */
    public function findContactDataByEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $contactsResponse = $this->findContactsByEmail($email);
        $contacts = $contactsResponse['data']['contacts'] ?? [];

        foreach ($contacts as $contact) {
            $contactEmail = strtolower(trim((string) ($contact['primary_email'] ?? $contact['communication']['primary_email'] ?? '')));
            $contactId = $contact['contact_id'] ?? $contact['basefields']['contact_id'] ?? null;
            if ($contactEmail !== $email || $contactId === null) {
                continue;
            }

            try {
                $response = $this->client()->request('GET', sprintf(
                    self::CONTACT_DATA_PATH,
                    $this->organizationId(),
                    (string) $contactId,
                ), [
                    'headers' => $this->headers(),
                ]);
            } catch (GuzzleException $exception) {
                throw new FairgateException('FSA contact data request failed.', 0, $exception);
            }

            return $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
        }

        return [
            'success' => true,
            'code' => 200,
            'data' => null,
        ];
    }

    private function client(): ClientInterface
    {
        return $this->httpClient ?? new Client([
            'base_uri' => rtrim($this->baseUrl(), '/') . '/',
            'timeout' => 10,
            'http_errors' => false,
        ]);
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Accept-Language' => 'de',
            'Authorization' => $this->bearerToken(),
        ];
    }

    private function bearerToken(): string
    {
        if ($this->bearerToken !== null) {
            return $this->bearerToken;
        }

        try {
            $response = $this->client()->request('POST', sprintf(self::TOKEN_PATH, $this->organizationId()), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'de',
                    'Content-Type' => 'application/json',
                ],
                'json' => ['access_key' => $this->accessKey()],
            ]);
        } catch (GuzzleException $exception) {
            throw new FairgateException('FSA authentication request failed.', 0, $exception);
        }

        $data = $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
        $token = $data['data']['token'] ?? null;
        if (!is_string($token) || $token === '' || !($this->tokenValidator)($token)) {
            throw new FairgateException('FSA authentication returned an invalid token.');
        }

        return $this->bearerToken = $token;
    }

    /** @return array<string, mixed> */
    private function decodeResponse(int $statusCode, string $body): array
    {
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new FairgateException(sprintf('FSA returned HTTP status %d.', $statusCode));
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new FairgateException('FSA returned invalid JSON.', 0, $exception);
        }

        if (!is_array($data) || ($data['success'] ?? true) === false) {
            throw new FairgateException('FSA returned an unsuccessful response.');
        }

        return $data;
    }

    private function validateToken(string $token): bool
    {
        try {
            $parsedToken = (new Parser(new JoseEncoder()))->parse($token);
            if (!$parsedToken instanceof UnencryptedToken || $parsedToken->isExpired(new \DateTimeImmutable())) {
                return false;
            }

            return (new Validator())->validate(
                $parsedToken,
                new SignedWith(new Sha512(), InMemory::plainText($this->publicKey())),
            );
        } catch (\Throwable) {
            return false;
        }
    }

    private function baseUrl(): string
    {
        return $this->baseUrl ?? getenv('FSA_BASE_URL') ?: 'https://fsa-test.fairgate.ch';
    }

    private function organizationId(): string
    {
        return $this->organizationId ?? $this->requiredEnvironment('FSA_ORGANIZATION_ID');
    }

    private function accessKey(): string
    {
        return $this->accessKey ?? $this->requiredEnvironment('FSA_ACCESS_KEY');
    }

    private function publicKey(): string
    {
        return str_replace('\\n', "\n", $this->publicKey ?? $this->requiredEnvironment('FSA_PUBLIC_KEY'));
    }

    private function requiredEnvironment(string $name): string
    {
        $value = getenv($name);
        if ($value === false || trim($value) === '') {
            throw new FairgateException(sprintf('Missing required FSA configuration: %s.', $name));
        }

        return $value;
    }
}
