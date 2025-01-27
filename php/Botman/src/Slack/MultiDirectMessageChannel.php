<?php
namespace Slack;

/**
 * Contains information about a direct message channel.
 */
class MultiDirectMessageChannel extends ClientObject implements ChannelInterface
{
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Gets the time the channel was created.
     *
     * @return \DateTime The time the channel was created.
     */
    public function getTimeCreated()
    {
        $time = new \DateTime();
        $time->setTimestamp($this->data['created']);
        return $time;
    }

    public function close()
    {
        return $this->client->apiCall('mpim.close', [
            'channel' => $this->getId(),
        ])->then(function ($response) {
            return !isset($response['no_op']);
        });
    }
}
