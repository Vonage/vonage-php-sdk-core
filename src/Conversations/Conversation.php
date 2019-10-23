<?php

namespace Nexmo\Conversations;

use Nexmo\User\User;
use Nexmo\Entity\Collection;
use Nexmo\Entity\EmptyFilter;
use Nexmo\Entity\FilterInterface;
use Nexmo\Conversations\Event\Event;
use Nexmo\Conversations\Member\Member;
use Nexmo\Entity\ArrayHydrateInterface;
use Nexmo\Conversations\Event\Client as EventClient;
use Nexmo\Conversations\Member\Client as MemberClient;

class Conversation implements ArrayHydrateInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var EventClient
     */
    protected $eventClient = null;

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var MemberClient
     */
    protected $memberClient;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string, string>
     */
    protected $properties = [];

    /**
     * @var \DateTimeImmutable
     */
    protected $timestamp;

    public function addEvent(Event $event)
    {
        return $this->eventClient->create($event);
    }

    public function addMember(User $user, $action = 'invite')
    {
        return $this->getMemberClient()->create($user, $action);
    }

    public function createFromArray($data)
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

        if (array_key_exists('image_url', $data)) {
            $this->setImageUrl($data['image_url']);
        }

        if (array_key_exists('properties', $data)) {
            $this->setProperties($data['properties']['custom_data']);
        }

        if (array_key_exists('timestamp', $data)) {
            if (is_array($data['timestamp']) && array_key_exists('created', $data['timestamp'])) {
                $this->setTimestamp(new \DateTimeImmutable($data['timestamp']['created']));
            } else {
                $this->setTimestamp(new \DateTimeImmutable($data['timestamp']));
            }
        }
    }

    public function deleteEvent(Event $event) : void
    {
        $this->getEventClient()->delete($event);
    }

    public function deleteMember(Member $member) : void
    {
        $this->getMemberClient()->delete($member);
    }

    public function toArray() : array
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'display_name' => $this->getDisplayName(),
            'image_url' => $this->getImageUrl(),
            'properties' => ['custom_data' => $this->getProperties()]
        ];

        if (!is_null($this->getTimestamp())) {
            $data['timestamp'] = ['created' => $this->getTimestamp()->format(\DateTimeInterface::RFC3339_EXTENDED)];
        }
        return $data;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getDisplayName() : ?string
    {
        return $this->displayName;
    }

    public function getEvent(string $id) : Event
    {
        return $this->getEventClient()->get($id);
    }

    public function searchEvents(FilterInterface $filter = null) : Collection
    {
        return $this->getEventClient()->search($filter);
    }

    public function getEventClient() : EventClient
    {
        if (!isset($this->eventClient)) {
            throw new \RuntimeException('Events Client was called but has not been configured');
        }

        return $this->eventClient;
    }

    public function getImageUrl() : ?string
    {
        return $this->imageUrl;
    }

    public function searchMembers(FilterInterface $filter = null) : Collection
    {
        return $this->getMemberClient()->search($filter);
    }

    public function getMember(string $id) : Member
    {
        return $this->getMemberClient()->get($id);
    }

    public function getMemberClient()
    {
        return $this->memberClient;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getProperty($key) : ?string
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }

        return null;
    }

    public function getTimestamp() : ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Convienance method to join a member to an existing conversation
     */
    public function joinMember(Member $member)
    {
        $member->setState('join');
        $member->setChannel('app');

        return $this->updateMember($member);
    }

    public function setId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function setDisplayName(string $displayName) : self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function setEventClient(EventClient $eventClient) : self
    {
        $this->eventClient = $eventClient;
        $this->eventClient->setConversation($this);

        return $this;
    }

    public function setImageUrl(string $url) : self
    {
        $this->imageUrl = $url;
        return $this;
    }

    public function setMemberClient(MemberClient $memberClient) : self
    {
        $this->memberClient = $memberClient;
        $this->memberClient->setConversation($this);
        return $this;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setProperties(array $properties) : self
    {
        $this->properties = $properties;
        return $this;
    }

    public function setProperty(string $key, string $value) : self
    {
        $this->properties[$key] = $value;
        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp) : self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function updateMember(Member $member) : Member
    {
        return $this->getMemberClient()->update($member);
    }
}
