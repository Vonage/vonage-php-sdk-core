<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function json_decode;
use function sprintf;

/**
 * @deprecated This data will be better exposed at the model level
 */
trait JsonResponseTrait
{
    protected $responseJson;

    /**
     * @throws Exception
     *
     * @return array|mixed
     */
    public function getResponseData()
    {
        if (!($this instanceof EntityInterface)) {
            throw new RuntimeException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    EntityInterface::class
                )
            );
        }

        /** @phpstan-ignore-next-line */
        if (($response = @$this->getResponse()) && ($response instanceof ResponseInterface)) {
            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }

            $body = $response->getBody()->getContents();
            $this->responseJson = json_decode($body, true);

            return $this->responseJson;
        }

        return [];
    }
}
