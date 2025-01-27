<?php

namespace Command;

use Services\SlackService;
use Slack\AutoChannel;
use Slack\Group;
use Slack\Message\Message;
use Slack\Message\MessageBuilder;
use Slack\Payload;
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

class HistoryCommand extends Command
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
            ->setName('bot:history');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = Factory::create();

        $clients = $this->slackService->getClients($loop, array('bigyouth', 'makheia'));
        $this->slackService
            ->getLastMessages($clients)
            ->then(function ($messages) use ($output) {
                foreach ($messages as $data) {
                    /** @var AutoChannel $channel */
                    list($channel, $message) = $data;

                    $time = new \DateTime();
                    $time->setTimestamp($message['ts']);

                    $output->writeln($time->format('Y-m-d H:i:s') . ' - ' . $message['text']);
                }
            });

        $loop->run();
    }
}