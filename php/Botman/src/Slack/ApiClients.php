<?php
namespace Slack;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Promise;
use Slack\ApiClient;
use Slack\Channel;
use Slack\ClientObject;
use Slack\Message\Message;
use Slack\Message\MessageBuilder;

class ApiClients
{
    /**
     * @var ApiClient[]
     */
    protected $clients = [];

    public function addClient($client)
    {
        $this->clients[] = $client;
    }

    /**
     * Gets all channels in the team.
     *
     * @return \React\Promise\PromiseInterface
     */
    public function getChannels($expr = null)
    {
        $promises = array_reduce($this->clients, function ($carry, $client) {
            /** @var ApiClient $client */
            $carry[] = $client->getChannels();
            $carry[] = $client->getGroups();
            $carry[] = $client->getDMs();
            $carry[] = $client->apiCall('mpim.list')->then(function ($response) use ($client) {
                $mdms = [];
                foreach ($response['groups'] as $group) {
                    $mdms[] = new MultiDirectMessageChannel($client, $group);

                }
                return $mdms;
            });

            return $carry;
        }, []);

        return Promise\reduce($promises, function ($carry, $channels) use ($expr) {
            $channels = array_map(function ($channel) {
                return new AutoChannel($channel);
            }, $channels);

            return array_merge($carry, $channels);
        }, [])->then(function ($channels) use ($expr) {
            return Promise\reduce($channels, function () use ($expr) {
//                if ($expr) {
//                    $channels = array_filter($channels, function ($channel) use ($expr) {
//                        /** @var AutoChannel $channel */
//                        $name = $channel->getName();
//
//                        return preg_match($expr, $name);
//                    });
//                }

            }, array());
        });
    }

    public function getHistories($expr = null)
    {
        /*return $this->getChannels($expr)
            ->then(function ($channels) {

            });
        $promises = array_reduce($this->getChannels($expr), function ($carry, $);
        return Promise\reduce($promises, function ($carry, $messages) {
            return array_merge($carry, $messages);
        }, []);*/
    }
}