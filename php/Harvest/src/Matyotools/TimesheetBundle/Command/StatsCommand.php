<?php

namespace Matyotools\TimesheetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('harvest:stats')
            ->setDescription('Stats harvest timesheet')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var \Matyotools\TimesheetBundle\Services\HarvestService $api */
        $api = $container->get('matyotools_timesheet.harvest');
        $days = $api->stats();

        $output->writeln("Stats timers");
        $output->writeln($api->display($days, $output));
    }
}
