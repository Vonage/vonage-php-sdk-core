<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Application;

use Exception;
use InvalidArgumentException;
use Vonage\Entity\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
    /**
     * @throws Exception
     */
    public function hydrate(array $data): Application
    {
        $application = new Application();
        return $this->hydrateObject($data, $application);
    }

    /**
     * @throws Exception
     */
    public function hydrateObject(array $data, $object): Application
    {
        if (isset($data['answer_url']) || isset($data['event_url'])) {
            return $this->createFromArrayV1($data, $object);
        }

        return $this->createFromArrayV2($data);
    }

    protected function createFromArrayV1(array $array, $application): Application
    {
        foreach (['name',] as $param) {
            if (!isset($array[$param])) {
                throw new InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $application->setName($array['name']);

        // Public key?
        if (isset($array['public_key'])) {
            $application->setPublicKey($array['public_key']);
        }

        // Voice
        foreach (['event', 'answer'] as $type) {
            $key = $type . '_url';

            if (isset($array[$key])) {
                $method = $array[$type . '_method'] ?? null;
                $application->getVoiceConfig()->setWebhook($key, new Webhook($array[$key], $method));
            }
        }

        // Messages
        foreach (['status', 'inbound'] as $type) {
            $key = $type . '_url';

            if (isset($array[$key])) {
                $method = $array[$type . '_method'] ?? null;
                $application->getMessagesConfig()->setWebhook($key, new Webhook($array[$key], $method));
            }
        }

        // RTC
        foreach (['event'] as $type) {
            $key = $type . '_url';

            if (isset($array[$key])) {
                $method = $array[$type . '_method'] ?? null;
                $application->getRtcConfig()->setWebhook($key, new Webhook($array[$key], $method));
            }
        }

        // VBC
        if (isset($array['vbc']) && $array['vbc']) {
            $application->getVbcConfig()->enable();
        }

        return $application;
    }

    /**
     * @throws Exception
     */
    protected function createFromArrayV2(array $array): Application
    {
        foreach (['name',] as $param) {
            if (!isset($array[$param])) {
                throw new InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $application = new Application();
        $application->fromArray($array);
        $application->setName($array['name']);

        // Is there a public key?
        if (isset($array['keys']['public_key'])) {
            $application->setPublicKey($array['keys']['public_key']);
        }

        // How about capabilities?
        if (!isset($array['capabilities'])) {
            return $application;
        }

        $capabilities = $array['capabilities'];

        // Handle voice
        if (isset($capabilities['voice'])) {
            $voiceCapabilities = $capabilities['voice']['webhooks'];

            foreach (['answer', 'event'] as $type) {
                $application->getVoiceConfig()->setWebhook($type . '_url', new Webhook(
                    $voiceCapabilities[$type . '_url']['address'],
                    $voiceCapabilities[$type . '_url']['http_method']
                ));
            }
        }

        // Handle messages
        if (isset($capabilities['messages'])) {
            $messagesCapabilities = $capabilities['messages']['webhooks'];

            foreach (['status', 'inbound'] as $type) {
                $application->getMessagesConfig()->setWebhook($type . '_url', new Webhook(
                    $messagesCapabilities[$type . '_url']['address'],
                    $messagesCapabilities[$type . '_url']['http_method']
                ));
            }
        }

        // Handle RTC
        if (isset($capabilities['rtc'])) {
            $rtcCapabilities = $capabilities['rtc']['webhooks'];

            foreach (['event'] as $type) {
                $application->getRtcConfig()->setWebhook($type . '_url', new Webhook(
                    $rtcCapabilities[$type . '_url']['address'],
                    $rtcCapabilities[$type . '_url']['http_method']
                ));
            }
        }

        // Handle VBC
        if (isset($capabilities['vbc'])) {
            $application->getVbcConfig()->enable();
        }

        return $application;
    }
}
