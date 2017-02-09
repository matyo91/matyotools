<?php

namespace bots;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\BotMan;
use React\EventLoop\Factory;
use React\Promise;
use Slack\ApiClient;
use Slack\Channel;
use Slack\User;

class TacosCommand extends Command
{
    /**
     * @var array
     */
    protected $configs;

    public function __construct($configs)
    {
        parent::__construct();

        $this->configs = $configs;
    }

    protected function configure()
    {
        $this
            ->setName('bot:tacos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = Factory::create();

        $client = new ApiClient($loop);
        $client->setToken($this->configs['bigyouth']['slack_token']);

        Promise\all([
            $client->getAuthedUser(),
            $client->getGroupByName('big-youth'),
        ])->then(function ($data) use ($client) {
            /** @var User $authedUser */
            $authedUser = $data[0];
            /** @var Channel $channel */
            $channel = $data[1];

            return $channel->getMembers()
                ->then(function ($members) use($authedUser) {
                    /** @var User[] $members */

                    //shuffle members without me
                    shuffle($members);
                    $members = array_filter($members, function (User $user) use($authedUser) {
                        return $user->getId() != $authedUser->getId();
                    });

                    return $members;
                })->then(function ($members) {
                    /** @var User[] $members */
                    return Promise\map($members, function ($member) {
                        /** @var User $member */
                        return $member->getPresence()->then(function($presence) use ($member) {
                            return [$member, $presence];
                        });
                    })->then(function ($members) {
                        $members = array_filter($members, function($data) {
                            return $data[1] == 'active';
                        });
                        $members = array_map(function ($data) {
                            return $data[0];
                        }, $members);

                        return $members;
                    });
                })->then(function ($members) use($client, $channel) {
                    /** @var User[] $members */

                    //get 5 members
                    $members = array_slice($members, 0, 5);
                    foreach ($members as $member)
                    {
                        $client->send("<@{$member->getId()}|{$member->getUsername()}> :taco:", $channel);
                    }

                    return $members;
                });
        })->then(function ($members) use ($output) {
            /** @var User[] $members */

            foreach ($members as $member) {
                $output->writeln('tacos sent to '.$member->getUsername());
            }
        });

        $loop->run();
    }
}