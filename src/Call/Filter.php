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

/**
 * @deprecated Use Vonage\Voice\Filter\VoiceFilter
 */
class Filter implements FilterInterface
{
    protected $query = [];

    /**
     * Filter constructor.
     */
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Filter is deprecated, please use Vonage\Voice\Filter\VoiceFilter instead',
            E_USER_DEPRECATED
        );
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return $this
     */
    public function sortAscending(): self
    {
        return $this->setOrder('asc');
    }

    /**
     * @return $this
     */
    public function sortDescending(): self
    {
        return $this->setOrder('desc');
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status): self
    {
        $this->query['status'] = (string)$status;

        return $this;
    }

    /**
     * @param DateTime $start
     * @return $this
     */
    public function setStart(DateTime $start): self
    {
        $start->setTimezone(new DateTimeZone("UTC"));
        $this->query['date_start'] = $start->format('Y-m-d\TH:i:s\Z');

        return $this;
    }

    /**
     * @param DateTime $end
     * @return $this
     */
    public function setEnd(DateTime $end): self
    {
        $end->setTimezone(new DateTimeZone("UTC"));
        $this->query['date_end'] = $end->format('Y-m-d\TH:i:s\Z');

        return $this;
    }

    /**
     * @param $size
     * @return $this
     */
    public function setSize($size): self
    {
        $this->query['page_size'] = (int)$size;

        return $this;
    }

    /**
     * @param $index
     * @return $this
     */
    public function setIndex($index): self
    {
        $this->query['record_index'] = (int)$index;

        return $this;
    }

    /**
     * @param $order
     * @return $this
     */
    public function setOrder($order): self
    {
        $this->query['order'] = (string)$order;

        return $this;
    }

    /**
     * @param $conversation
     * @return $this
     */
    public function setConversation($conversation): self
    {
        if ($conversation instanceof Conversation) {
            $conversation = $conversation->getId();
        }

        $this->query['conversation_uuid'] = $conversation;

        return $this;
    }
}
