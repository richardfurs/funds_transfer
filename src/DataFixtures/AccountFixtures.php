<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Account;
use App\Entity\Client;
use Faker\Factory;
use App\Config\Currency;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $currencies = array_map(fn($c) => $c->value, Currency::cases());

        for($i = 1; $i <= 30; $i++) {
            $account = new Account();
            $client = $this->getReference("client_{$faker->numberBetween(1, 10)}", Client::class);

            $randomKey = array_rand($currencies);

            $account->setClient($client);
            $account->setBalance($faker->randomFloat(2, 0, 9999999.99));
            $account->setCurrency($currencies[$randomKey]);

            $this->addReference("account_{$i}", $account);
            $manager->persist($account);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
        ];
    }
}
