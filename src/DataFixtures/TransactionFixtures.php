<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 1; $i <= 150; $i++) {
            $transaction = new Transaction();

            $accountFrom = $this->getReference("account_{$faker->numberBetween(1, 30)}", Account::class);

            do {
                $accountTo = $this->getReference("account_{$faker->numberBetween(1, 30)}", Account::class);
            } while ($accountFrom->getId() === $accountTo->getId());

            $transaction->setFromAccount($accountFrom);
            $transaction->setToAccount($accountTo);
            $transaction->setAmount($faker->randomFloat(2, 0, 99999.99));
            $transaction->setCurrency($accountTo->getCurrency());
            $transaction->setExchangeRate($faker->randomFloat(2, 0, 99.999999));

            $manager->persist($transaction);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
