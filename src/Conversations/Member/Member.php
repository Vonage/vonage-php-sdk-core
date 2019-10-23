<?php

namespace Nexmo\Conversations\Member;

use Nexmo\Entity\ArrayHydrateInterface;

class Member implements ArrayHydrateInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $conversationId;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var \DateTimeImmutable
     */
    protected $timestampInvited;

    /**
     * @var \DateTimeImmutable
     */
    protected $timestampJoined;

    /**
     * @var \DateTimeImmutable
     */
    protected $timestampLeft;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var bool
     */
    protected $invited;

    /**
     * @var bool
     */
    protected $joined;

    /**
     * @var array
     */
    protected $media = [
        'audio_settings' => [
            'enabled' => true,
            'earmuffed' => false,
            'muted' => true,
        ]
    ];

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getDisplayName() : ?string
    {
        return $this->displayName;
    }

    public function getUserId() : ?string
    {
        return $this->userId;
    }

    public function getConversationId() : ?string
    {
        return $this->conversationId;
    }

    public function getState() : ?string
    {
        return $this->state;
    }

    public function getTimestampInvited() : ?\DateTimeImmutable
    {
        return $this->timestampInvited;
    }

    public function getTimestampJoined() : ?\DateTimeImmutable
    {
        return $this->timestampJoined;
    }

    public function getTimestampLeft() : ?\DateTimeImmutable
    {
        return $this->timestampLeft;
    }

    public function getChannel() : ?string
    {
        return $this->channel;
    }

    public function getInvited() : bool
    {
        return $this->invited;
    }

    public function getJoined() : bool
    {
        return $this->joined;
    }

    public function getMedia() : array
    {
        return $this->media;
    }

    public function setId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setDisplayName(string $displayName) : self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function setUserId(string $userId) : self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setConversationId(string $conversationId) : self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function setState(string $state) : self
    {
        $this->state = $state;
        return $this;
    }

    public function setTimestampInvited(\DateTimeImmutable $timestampInvited) : self
    {
        $this->timestampInvited = $timestampInvited;
        return $this;
    }

    public function setTimestampJoined(\DateTimeImmutable $timestampJoined) : self
    {
        $this->timestampJoined = $timestampJoined;
        return $this;
    }

    public function setTimestampLeft(\DateTimeImmutable $timestampLeft) : self
    {
        $this->timestampLeft = $timestampLeft;
        return $this;
    }

    public function setChannel(string $channel) : self
    {
        $this->channel = $channel;
        return $this;
    }

    public function setInvited(bool $invited) : self
    {
        $this->invited = $invited;
        return $this;
    }

    public function setJoined(bool $joined) : self
    {
        $this->joined = $joined;
        return $this;
    }

    public function setMedia(array $media) : self
    {
        $this->media = $media;
        return $this;
    }

    public function createFromArray(array $data)
    {
        if (array_key_exists('id', $data)) {
            $this->setId($data['id']);
        }
        
        if (array_key_exists('name', $data)) {
            $this->setName($data['name']);
        }

        if (array_key_exists('display_name', $data)) {
            $this->setDisplayName($data['display_name']);
        }

        if (array_key_exists('user_id', $data)) {
            $this->setUserId($data['user_id']);
        }

        if (array_key_exists('conversation_id', $data)) {
            $this->setConversationId($data['conversation_id']);
        }

        if (array_key_exists('state', $data)) {
            $this->setState($data['state']);
        }

        if (array_key_exists('timestamp', $data)) {
            if (array_key_exists('invited', $data['timestamp'])) {
                $this->setTimestampInvited(new \DateTimeImmutable($data['timestamp']['invited']));
            }
            
            if (array_key_exists('joined', $data['timestamp'])) {
                $this->setTimestampJoined(new \DateTimeImmutable($data['timestamp']['joined']));
            }

            if (array_key_exists('left', $data['timestamp'])) {
                $this->setTimestampLeft(new \DateTimeImmutable($data['timestamp']['left']));
            }
        }

        if (array_key_exists('channel', $data)) {
            $this->setChannel($data['channel']['type']);
        }

        if (array_key_exists('initiator', $data)) {
            if (array_key_exists('invited', $data['initiator'])) {
                $this->setInvited($data['initiator']['invited']['is_system']);
            }

            if (array_key_exists('joined', $data['initiator'])) {
                $this->setJoined($data['initiator']['joined']['is_system']);
            }
        }

        if (array_key_exists('media', $data)) {
            $this->setMedia($data['media']);
        }
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'display_name' => $this->getDisplayName(),
            'user_id' => $this->getUserId(),
            'conversation_id' => $this->getConversationId(),
            'state' => $this->getState(),
            'media' => $this->getMedia(),
            'initiator' => [
                'invited' => ['is_system' => $this->getInvited()],
                'joined' => ['is_system' => $this->getJoined()]
            ],
        ];

        if (!is_null($this->getTimestampInvited())) {
            $data['timestamp']['invited'] = $this->getTimestampInvited()->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        if (!is_null($this->getTimestampJoined())) {
            $data['timestamp']['joined'] = $this->getTimestampJoined()->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        if (!is_null($this->getTimestampLeft())) {
            $data['timestamp']['left'] = $this->getTimestampLeft()->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        return $data;
    }
}
