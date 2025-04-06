<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Rate;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Config\Currency;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:update-rates',
    description: 'Add a short description for your command',
)]
class UpdateRatesCommand extends Command
{
    public function __construct(private HttpClientInterface $client, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symbols = array_map(fn($c) => $c->value, Currency::cases());
        
        foreach (Currency::cases() as $from) {
            $filtered = array_filter($symbols, fn($s) => $s !== $from->value);
            $filteredSymbols = implode(',', $filtered);
            $url = "https://api.frankfurter.app/latest?from={$from->value}&to={$filteredSymbols}";

            $response = $this->client->request('GET', $url);

            $data = $response->toArray();

            foreach ($data['rates'] as $currency => $apiRate) {
                $rate = new Rate();
                $rate->setBaseCurrency($from->value);
                $rate->setTargetCurrency($currency);
                $rate->setRate($apiRate);

                $this->entityManager->persist($rate);
            }
        }

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);

        $io->success('Latest currency rates saved.');

        return Command::SUCCESS;
    }
}
