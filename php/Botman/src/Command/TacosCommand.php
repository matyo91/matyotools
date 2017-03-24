<?php

namespace Command;

use Services\SlackService;
use Slack\AutoChannel;
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
     * @var SlackService
     */
    protected $slackService;

    public function __construct(SlackService $slackService)
    {
        parent::__construct();

        $this->slackService = $slackService;
    }

    protected function configure()
    {
        $this
            ->setName('bot:tacos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = Factory::create();

        $clients = $this->slackService->getClients($loop, 'bigyouth');

        $this->slackService
            ->getChannels($clients, 'big-youth')
            ->then(function ($channels) {
                return Promise\map($channels, function ($channel) {
                    /** @var AutoChannel $channel */
                    return Promise\all(array(
                        $channel,
                        $channel->getClient()->getAuthedUser(),
                        Promise\map($channel->getMembers(), function ($member) {
                            /** @var User $member */
                            return Promise\all(array(
                                $member,
                                $member->getPresence(),
                            ));
                        }),
                    ));
                });
            })
            ->then(function ($datas) {
                foreach ($datas as $data) {
                    /** @var User $authedUser */
                    /** @var Channel $channel */
                    /** @var User[] $members */
                    list($authedUser, $channel, $members) = $data;

                    $client = $channel->getClient();

                    //shuffle members
                    shuffle($members);

                    //without me
                    $members = array_filter($members, function ($member) use ($authedUser) {
                        /** @var User $user */
                        list($user, $presence) = $member;
                        return $user->getId() != $authedUser->getId();
                    });

                    //without heytaco bot
                    $members = array_filter($members, function ($member) {
                        /** @var User $user */
                        list($user, $presence) = $member;
                        return $user->getUsername() !== 'heytaco';
                    });

                    //connected users
                    $members = array_filter($members, function ($member) {
                        /** @var User $user */
                        list($user, $presence) = $member;
                        return $presence == 'active';
                    });

                    //get 5 members
                    $members = array_slice($members, 0, 5);
                    
                    return array_map(function ($member) { return $member[0]; }, $members);
                }
            })->then(function ($users) use ($output) {
                /** @var User[] $users */
                foreach ($users as $user) {
                    //$client->send("<@{$user->getId()}|{$user->getUsername()}> :taco:", $channel);
                }

                foreach ($users as $user) {
                    $output->writeln('tacos sent to ' . $user->getUsername());
                }
            });

        $loop->run();
    }
}