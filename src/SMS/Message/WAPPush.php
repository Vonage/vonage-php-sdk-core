<?php
declare(strict_types=1);

namespace Vonage\SMS\Message;

class WAPPush extends OutboundMessage
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type = 'wappush';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $validity;

    public function __construct(string $to, string $from, string $title, string $url, int $validity)
    {
        parent::__construct($to, $from);
        $this->title = $title;
        $this->url = $url;
        $this->validity = $validity;
    }

    public function toArray(): array
    {
        $data = [
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'validity' => $this->getValidity(),
        ];

        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getValidity() : int
    {
        return $this->validity;
    }
}
