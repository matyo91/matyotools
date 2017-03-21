<?php
namespace Slack;

use React\Promise;

/**
 * Represents a single Slack channel.
 */
class AutoChannel extends ClientObject implements ChannelInterface
{
    /**
     * @var Channel|Group|DirectMessageChannel|MultiDirectMessageChannel
     */
    private $instance;

    public function __construct(ClientObject $instance)
    {
        parent::__construct($instance->getClient(), $instance->data);

        $this->instance = $instance;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function getId()
    {
        return $this->instance->getId();
    }

    public function close()
    {
        return $this->instance->close();
    }

    public function getName()
    {
        if ($this->instance instanceof Channel || $this->instance instanceof Group) {
            return $this->instance->getName();
        } elseif ($this->instance instanceof DirectMessageChannel) {
            return $this->instance->getUser()->then(function ($user) {
                /** @var User $user */
                return $user->getUsername();
            });
        } elseif ($this->instance instanceof MultiDirectMessageChannel) {
            return $this->data['name'];
        }

        return '';
    }

    public function getMembers()
    {
        if ($this->instance instanceof Channel || $this->instance instanceof Group) {
            return $this->instance->getMembers();
        } elseif ($this->instance instanceof DirectMessageChannel || $this->instance instanceof MultiDirectMessageChannel) {
            $memberPromises = [];
            foreach ($this->data['members'] as $memberId) {
                $memberPromises[] = $this->client->getUserById($memberId);
            }

            return Promise\all($memberPromises);
        }

        return array();
    }
}