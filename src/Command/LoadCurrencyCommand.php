<?php

namespace App\Command;

use App\Service\CurrencyLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadCurrencyCommand extends Command
{
    protected static $defaultName = 'currencies:load';
    protected static $defaultDescription = 'Load currencies';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = new \DateTime();
        $loader = new CurrencyLoader($this->em);
        $currencies = $loader->loadCurrencies($date);

        if ($currencies != null && !empty($currencies)) {
            $io->success('Load currencies');
            return Command::SUCCESS;
        } else {
            $io->error('No currencies loaded');
            return Command::FAILURE;
        }
    }

}
