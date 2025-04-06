<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Client;
use Faker\Factory;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->setName($faker->name);
            $client->setEmail($faker->email);
            
            $this->addReference("client_{$i}", $client);
            $manager->persist($client);
        }

        $manager->flush();
    }
}
