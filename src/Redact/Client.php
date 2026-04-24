<?php

declare(strict_types=1);

namespace Vonage\Redact;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;

use function preg_replace;
use function str_replace;

class Client
{
    /**
     * @todo Stop having this use its own formatting for exceptions
     */
    public function __construct(protected APIResource $api) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function transaction(string $id, string $product, array $options = []): void
    {
        $this->api->setBaseUri('/v1/redact/transaction');

        $body = ['id' => $id, 'product' => $product] + $options;
        try {
            $this->api->create($body);
        } catch (RequestException $e) {
            $message = preg_replace('/: /', ' - ', $e->getMessage(), 1) ?? $e->getMessage();
            $message = str_replace(' for more information', '', $message);

            throw new RequestException($message, $e->getCode(), $e);
        }
    }
}
