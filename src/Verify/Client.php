<?php

declare(strict_types=1);

namespace Vonage\Verify;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;

use function is_array;
use function is_string;
use function serialize;
use function trigger_error;
use function unserialize;

class Client
{
    public function __construct(protected APIResource $api) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function search(string $verificationId): Verification
    {
        $params = [
            'request_id' => $verificationId
        ];

        $data = $this->api->create($params, '/search/json');

        $verification = new Verification();
        $verification->fromArray($data);

        return $this->checkError($verification, $data);
    }

    /**
     * @param $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function cancel($verification): Verification
    {
        if ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object to Vonage\Verification\Client::cancel() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        return $this->control($verification, 'cancel');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function check(string $verification, string $code, ?string $ip = null): Verification
    {
        $params = [
            'request_id' => $verification,
            'code' => $code
        ];

        if ($ip !== null) {
            $params['ip'] = $ip;
        }

        $data = $this->api->create($params, '/check/json');

        $v = new Verification();
        $v->fromArray($data);

        return $this->checkError($v, $data);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function startVerification(StartVerification $request): string
    {
        $response = $this->api->create($request->toArray(), '/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function startPsd2Verification(StartPSD2 $request): string
    {
        $response = $this->api->create($request->toArray(), '/psd2/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function triggerNextEvent(string $requestId): bool
    {
        $data = $this->api->create(
            ['request_id' => $requestId, 'cmd' => 'trigger_next_event'],
            '/control/json'
        );

        return ($data['status'] ?? '') === '0';
    }

    /**
     * @deprecated Serialize the Verification object directly instead
     */
    public function serialize(Verification $verification): string
    {
        trigger_error(
            static::class . '::serialize() is deprecated, serialize the Verification object directly',
            E_USER_DEPRECATED
        );

        return serialize($verification);
    }

    /**
     * @param $verification
     */
    public function unserialize($verification): Verification
    {
        trigger_error(
            static::class . '::unserialize() is deprecated, unserialize the Verification object directly',
            E_USER_DEPRECATED
        );

        if (is_string($verification)) {
            $verification = unserialize($verification, [Verification::class]);
        }

        if (!($verification instanceof Verification)) {
            throw new InvalidArgumentException('expected verification object or serialize verification object');
        }

        @$verification->setClient($this);
        return $verification;
    }

    /**
     * @param string $cmd Next command to execute, must be `cancel` or `trigger_next_event`
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    protected function control(Verification|array|string $verification, string $cmd): Verification
    {
        if (is_array($verification)) {
            trigger_error(
                'Passing an array for parameter 1 to Vonage\Verification\Client::control() is deprecated,' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
            $requestId = $verification['request_id'] ?? '';
        } elseif ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object for parameter 1 to Vonage\Verification\Client::control() ' .
                'is deprecated, please pass a string ID instead',
                E_USER_DEPRECATED
            );
            $requestId = $verification->getRequestId();
        } else {
            $requestId = $verification;
        }

        $params = [
            'request_id' => $requestId,
            'cmd' => $cmd
        ];

        $data = $this->api->create($params, '/control/json');

        $v = new Verification(requestId: $requestId);
        $v->fromArray($data + ['request_id' => $requestId]);

        return $this->checkError($v, $data);
    }

    /**
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    protected function checkError($verification, array $data)
    {
        if (!isset($data['status'])) {
            $e = new ClientException\RequestException('unexpected response from API');
            $e->setEntity($data);

            throw $e;
        }

        //normalize errors (client vrs server)
        switch ($data['status']) {
            // These exist because `status` is valid in both the error
            // response and a success response, but serve different purposes
            // in each case
            case 'IN PROGRESS':
            case 'SUCCESS':
            case 'FAILED':
            case 'EXPIRED':
            case 'CANCELLED':
            case '0':
                return $verification;
            case '5':
                $e = new ClientException\ServerException($data['error_text'], (int)$data['status']);
                $e->setEntity($data);
                break;
            default:
                $e = new ClientException\RequestException($data['error_text'], (int)$data['status']);
                $e->setEntity($data);

                if (array_key_exists('request_id', $data)) {
                    $e->setRequestId($data['request_id']);
                }

                if (array_key_exists('network', $data)) {
                    $e->setNetworkId($data['network']);
                }

                break;
        }

        $e->setEntity($verification);

        throw $e;
    }
}
