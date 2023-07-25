<?php

declare(strict_types=1);

namespace VonageTest\Users;

use Vonage\Users\User;
use VonageTest\VonageTestCase;

class UserTest extends VonageTestCase
{
    private User $user;
    private array $testData = [
        'id' => '1',
        'name' => 'Test User',
        'display_name' => 'Test Display Name',
        'image_url' => 'https://test.com/image.jpg',
        'properties' => ['prop1' => 'value1', 'prop2' => 'value2'],
        'channels' => ['channel1', 'channel2'],
        '_links' => [
            'self' => [
                'href' => 'https://test.com/user/1'
            ]
        ]
    ];

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetSetId(): void
    {
        $this->user->setId($this->testData['id']);
        $this->assertEquals($this->testData['id'], $this->user->getId());
    }

    public function testGetSetName(): void
    {
        $this->user->setName($this->testData['name']);
        $this->assertEquals($this->testData['name'], $this->user->getName());
    }

    public function testGetSetDisplayName(): void
    {
        $this->user->setDisplayName($this->testData['display_name']);
        $this->assertEquals($this->testData['display_name'], $this->user->getDisplayName());
    }

    public function testGetSetImageUrl(): void
    {
        $this->user->setImageUrl($this->testData['image_url']);
        $this->assertEquals($this->testData['image_url'], $this->user->getImageUrl());
    }

    public function testGetSetProperties(): void
    {
        $this->user->setProperties($this->testData['properties']);
        $this->assertEquals($this->testData['properties'], $this->user->getProperties());
    }

    public function testGetSetChannels(): void
    {
        $this->user->setChannels($this->testData['channels']);
        $this->assertEquals($this->testData['channels'], $this->user->getChannels());
    }

    public function testGetSetSelfLink(): void
    {
        $this->user->setSelfLink($this->testData['_links']['self']['href']);
        $this->assertEquals($this->testData['_links']['self']['href'], $this->user->getSelfLink());
    }

    public function testFromArray(): void
    {
        $this->user->fromArray($this->testData);
        $this->assertEquals($this->testData['id'], $this->user->getId());
        $this->assertEquals($this->testData['name'], $this->user->getName());
        $this->assertEquals($this->testData['display_name'], $this->user->getDisplayName());
        $this->assertEquals($this->testData['image_url'], $this->user->getImageUrl());
        $this->assertEquals($this->testData['properties'], $this->user->getProperties());
        $this->assertEquals($this->testData['channels'], $this->user->getChannels());
        $this->assertEquals($this->testData['_links']['self']['href'], $this->user->getSelfLink());
    }

    public function testToArray(): void
    {
        $this->user->fromArray($this->testData);
        $toArrayData = $this->user->toArray();
        $this->assertEquals($this->testData, $toArrayData);
    }
}
