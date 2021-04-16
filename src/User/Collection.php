<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\User;

use ArrayAccess;
use Exception;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\CollectionInterface;
use Vonage\Entity\CollectionTrait;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\NoRequestResponseTrait;

use function is_null;
use function json_decode;
use function json_encode;

/**
 * @deprecated This will be removed in a future version, as this API is still considered Beta
 */
class Collection implements ClientAwareInterface, CollectionInterface, ArrayAccess
{
    use ClientAwareTrait;
    use CollectionTrait;
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    public static function getCollectionName(): string
    {
        return 'users';
    }

    public static function getCollectionPath(): string
    {
        return '/beta/' . self::getCollectionName();
    }

    /**
     * @param $data
     * @param $idOrUser
     *
     * @return mixed|User
     */
    public function hydrateEntity($data, $idOrUser)
    {
        if (!($idOrUser instanceof User)) {
            $idOrUser = new User($idOrUser);
        }

        $idOrUser->setClient($this->getClient());
        $idOrUser->jsonUnserialize($data);

        return $idOrUser;
    }

    /**
     * @param $users
     */
    public function hydrateAll($users): array
    {
        $hydrated = [];
        foreach ($users as $u) {
            $key = isset($u['user_id']) ? 'user_id' : 'id';
            $user = new User($u[$key]);

            // Setting the client makes us run out of memory and I'm not sure why yet
            // $idOrUser->setClient($this->getClient());

            $user->jsonUnserialize($u);
            $hydrated[] = $user;
        }

        return $hydrated;
    }

    /**
     * @return $this
     */
    public function __invoke($filter = null)
    {
        if (!is_null($filter)) {
            $this->setFilter($filter);
        }

        return $this;
    }

    public function fetch(): array
    {
        $this->fetchPage(self::getCollectionPath());
        return $this->hydrateAll($this->page);
    }

    /**
     * @param $user
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function create($user): User
    {
        return $this->post($user);
    }

    /**
     * @param $user
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function post($user): User
    {
        if ($user instanceof User) {
            $body = $user->getRequestData();
        } else {
            $body = $user;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . self::getCollectionPath(),
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->client->send($request);

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        $user = new User($body['id']);
        $user->jsonUnserialize($body);
        $user->setClient($this->getClient());

        return $user;
    }

    /**
     * @param $user
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function get($user): User
    {
        if (!($user instanceof User)) {
            $user = new User($user);
        }

        $user->setClient($this->getClient());
        $user->get();

        return $user;
    }

    /**
     * @throws ClientException\Exception
     *
     * @return ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = $body['code'] ?? 'Unexpected error';

        if (isset($body['description']) && $body['description']) {
            $errorTitle = $body['description'];
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($errorTitle, $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    public function offsetExists($offset): bool
    {
        return true;
    }

    public function offsetGet($user): User
    {
        if (!($user instanceof User)) {
            $user = new User($user);
        }

        $user->setClient($this->getClient());
        return $user;
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('can not set collection properties');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('can not unset collection properties');
    }

    /**
     * Handle pagination automatically (unless configured not to).
     */
    public function valid(): bool
    {
        //can't be valid if there's not a page (rewind sets this)
        if (!isset($this->page)) {
            return false;
        }

        if (isset($this->page['_embedded'])) {
            //all hal collections have an `_embedded` object, we expect there to be a property matching the collection name
            if (!isset($this->page['_embedded'][static::getCollectionName()])) {
                return false;
            }

            //if we have a page with no items, we've gone beyond the end of the collection
            if (!count($this->page['_embedded'][static::getCollectionName()])) {
                return false;
            }

            //index the start of a page at 0
            if (is_null($this->current)) {
                $this->current = 0;
            }

            //if our current index is past the current page, fetch the next page if possible and reset the index
            if (!isset($this->page['_embedded'][static::getCollectionName()][$this->current])) {
                if (isset($this->page['_links']['next'])) {
                    $this->fetchPage($this->page['_links']['next']['href']);
                    $this->current = 0;

                    return true;
                }

                return false;
            }
        } else {
            if (!isset($this->page)) {
                return false;
            }

            //index the start of a page at 0
            if (is_null($this->current)) {
                $this->current = 0;
            }

            //if our current index is past the current page, fetch the next page if possible and reset the index
            if (!isset($this->page[$this->current])) {
                if (isset($this->page['_links']['next'])) {
                    $this->fetchPage($this->page['_links']['next']['href']);
                    $this->current = 0;

                    return true;
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Return the current item, expects concrete collection to handle creating the object.
     */
    public function current(): User
    {
        if (isset($this->page['_embedded'])) {
            return $this->hydrateEntity($this->page['_embedded'][static::getCollectionName()][$this->current], $this->key());
        } else {
            return $this->hydrateEntity($this->page[$this->current], $this->key());
        }
    }
}
