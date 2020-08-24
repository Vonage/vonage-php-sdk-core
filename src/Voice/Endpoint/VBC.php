<?php
declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class VBC implements EndpointInterface
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $extension)
    {
        $this->id = $extension;
    }

    public static function factory(string $extension) : VBC
    {
        return new VBC($extension);
    }

    /**
     * @return array{type: string, user: string}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, user: string}
     */
    public function toArray() : array
    {
        return [
            'type' => 'vbc',
            'extension' => $this->id,
        ];
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}
