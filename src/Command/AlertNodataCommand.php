<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\SimplementWeb\SettingsBundle\Service\Setting;
use Goutte\Client;

class AlertNodataCommand extends Command
{
    protected static $defaultName = 'app:alert:nodata';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    public function __construct(Setting $setting)
    {
        parent::__construct();
        $this->setting = $setting;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = new Client();

        if (date('h') < 8) {
            return 0;
        }

        $last_temperature = new \DateTime($this->setting->get('last_temperature'));
        $last_alert = new \DateTime($this->setting->get('last_alert'));
        if (($last_temperature < new \DateTime('-30 minutes')) && $last_temperature > $last_alert) {
            $msg = sprintf('Pas de relevé de température depuis %s (le %s)', $last_temperature->format('H:i'), $last_temperature->format('d/m'));
            $io->error($msg);
            $url = sprintf(
                'https://smsapi.free-mobile.fr/sendmsg?user=%s&pass=%s&msg=%s',
                $this->setting->get('freemobile_user'),
                $this->setting->get('freemobile_pass'),
                urlencode($msg)
            );
            $client->request('GET', $url);
            $now = new \DateTime('now');
            $this->setting->set('last_alert', $now->format('Y-m-d H:i:s'));
        }

        return 0;
    }
}
