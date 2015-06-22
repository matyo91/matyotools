<?php

namespace Matyotools\TimesheetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DominoCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('harvest:domino')
			->setDescription('Domino timesheet')
			->addOption('week', null, InputArgument::OPTIONAL, 'Semaine relative')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();

		/** @var \Matyotools\DominoBundle\Services\DominoWebService $dominoWeb */
		/*$dominoWeb = $container->get('domino_web');
		$dominoWeb->login();
        $dominoWeb->fill();*/

		$date = null;
		$week = $input->getOption('week');
		if($week) {
			$date = new \DateTime($week.' weeks ago');
		}

		/** @var \Matyotools\DominoBundle\Services\DominoDriveService $dominoDrive */
		$dominoDrive = $container->get('domino_drive');
		$timesheet = $dominoDrive->drive($date);

		$rows = array_map(function($line) {
			return array(
				$line['project']['name'],
				$line['monday'],
				$line['tuesday'],
				$line['wednesday'],
				$line['thursday'],
				$line['friday'],
				$line['saturday'],
				$line['sunday'],
			);
		}, $timesheet);

		$table = $this->getHelperSet()->get('table');
		$table
			->setHeaders(array('', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'))
			->setRows($rows)
		;
		$table->render($output);

		$output->writeln('URL : https://dominoweb.domino-info.fr:7001/cgiphl/pw_main.pgm');
	}
}
