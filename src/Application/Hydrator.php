<?php

namespace Vonage\Application;

use Vonage\Entity\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $application = new Application();
        return $this->hydrateObject($data, $application);
    }

    public function hydrateObject(array $data, $object)
    {
        if (isset($data['answer_url']) || isset($data['event_url'])) {
            return $this->createFromArrayV1($data, $object);
        }

        return $this->createFromArrayV2($data, $object);
    }

    protected function createFromArrayV1(array $array, $application) : Application
    {
        foreach (['name',] as $param) {
            if (!isset($array[$param])) {
                throw new \InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $application->setName($array['name']);

        // Public key?
        if (isset($array['public_key'])) {
            $application->setPublicKey($array['public_key']);
        }

        // Voice
        foreach (['event', 'answer'] as $type) {
            if (isset($array[$type . '_url'])) {
                $method = isset($array[$type . '_method']) ? $array[$type . '_method'] : null;
                $application->getVoiceConfig()->setWebhook($type . '_url', new Webhook($array[$type . '_url'], $method));
            }
        }

        // Messages
        foreach (['status', 'inbound'] as $type) {
            if (isset($array[$type . '_url'])) {
                $method = isset($array[$type . '_method']) ? $array[$type . '_method'] : null;
                $application->getMessagesConfig()->setWebhook($type . '_url', new Webhook($array[$type . '_url'], $method));
            }
        }

        // RTC
        foreach (['event'] as $type) {
            if (isset($array[$type . '_url'])) {
                $method = isset($array[$type . '_method']) ? $array[$type . '_method'] : null;
                $application->getRtcConfig()->setWebhook($type . '_url', new Webhook($array[$type . '_url'], $method));
            }
        }

        // VBC
        if (isset($array['vbc']) && $array['vbc']) {
            $application->getVbcConfig()->enable();
        }

        return $application;
    }

    protected function createFromArrayV2(array $array) : Application
    {
        foreach (['name',] as $param) {
            if (!isset($array[$param])) {
                throw new \InvalidArgumentException('missing expected key `' . $param . '`');
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
                $application->getVoiceConfig()->setWebhook($type.'_url', new Webhook(
                    $voiceCapabilities[$type.'_url']['address'],
                    $voiceCapabilities[$type.'_url']['http_method']
                ));
            }
        }

        // Handle messages
        if (isset($capabilities['messages'])) {
            $messagesCapabilities = $capabilities['messages']['webhooks'];

            foreach (['status', 'inbound'] as $type) {
                $application->getMessagesConfig()->setWebhook($type.'_url', new Webhook(
                    $messagesCapabilities[$type.'_url']['address'],
                    $messagesCapabilities[$type.'_url']['http_method']
                ));
            }
        }

        // Handle RTC
        if (isset($capabilities['rtc'])) {
            $rtcCapabilities = $capabilities['rtc']['webhooks'];

            foreach (['event'] as $type) {
                $application->getRtcConfig()->setWebhook($type.'_url', new Webhook(
                    $rtcCapabilities[$type.'_url']['address'],
                    $rtcCapabilities[$type.'_url']['http_method']
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
