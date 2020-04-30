<?php
declare(strict_types=1);

namespace Nexmo\Account;

use Nexmo\InvalidResponseException;

class Secret
{
    /**
     * @var string
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array<string, array>
     */
    protected $links;

    /**
     * @param array<string, array> $links External links from the API response
     */
    public function __construct(string $id, string $createdAt, array $links)
    {
        $this->createdAt = $createdAt;
        $this->id = $id;
        $this->links = $links;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getCreatedAt() : string
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, array>
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    public function __get($key)
    {
        if ($key === 'data') {
            trigger_error(
                "Direct access to " . get_class($this) . "::data is deprecated, please use getter to toArray() methods",
                E_USER_DEPRECATED
            );
            return $this->data;
        }
    }
}
