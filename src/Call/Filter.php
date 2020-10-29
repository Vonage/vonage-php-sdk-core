<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use DateTime;
use DateTimeZone;
use Vonage\Conversations\Conversation;
use Vonage\Entity\Filter\FilterInterface;

use function trigger_error;

/**
 * @deprecated Use Vonage\Voice\Filter\VoiceFilter
 */
class Filter implements FilterInterface
{
    /**
     * @var array
     */
    protected $query = [];

    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Filter is deprecated, please use Vonage\Voice\Filter\VoiceFilter instead',
            E_USER_DEPRECATED
        );
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function sortAscending(): self
    {
        return $this->setOrder('asc');
    }

    public function sortDescending(): self
    {
        return $this->setOrder('desc');
    }

    public function setStatus($status): self
    {
        $this->query['status'] = (string)$status;

        return $this;
    }

    public function setStart(DateTime $start): self
    {
        $start->setTimezone(new DateTimeZone("UTC"));
        $this->query['date_start'] = $start->format('Y-m-d\TH:i:s\Z');

        return $this;
    }

    public function setEnd(DateTime $end): self
    {
        $end->setTimezone(new DateTimeZone("UTC"));
        $this->query['date_end'] = $end->format('Y-m-d\TH:i:s\Z');

        return $this;
    }

    /**
     * @param string|int $size
     */
    public function setSize($size): self
    {
        $this->query['page_size'] = (int)$size;

        return $this;
    }

    /**
     * @param string|int $index
     */
    public function setIndex($index): self
    {
        $this->query['record_index'] = (int)$index;

        return $this;
    }

    public function setOrder(string $order): self
    {
        $this->query['order'] = $order;

        return $this;
    }

    public function setConversation($conversation): self
    {
        if ($conversation instanceof Conversation) {
            $conversation = $conversation->getId();
        }

        $this->query['conversation_uuid'] = $conversation;

        return $this;
    }
}
