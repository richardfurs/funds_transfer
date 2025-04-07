<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Client;
use App\Entity\Account;

final class ClientControllerTest extends WebTestCase
{
    public function testAccounts(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
    
        $testClient = new Client();
        $testClient->setName('Test Client');
        $testClient->setEmail('test@mail.com');
    
        $account1 = new \App\Entity\Account();
        $account1->setCurrency('USD')->setBalance(100)->setClient($testClient);
    
        $account2 = new \App\Entity\Account();
        $account2->setCurrency('EUR')->setBalance(200)->setClient($testClient);
    
        $em->persist($testClient);
        $em->persist($account1);
        $em->persist($account2);
        $em->flush();
    
        $client->request('GET', '/clients/' . $testClient->getId() . '/accounts');
    
        $response = $client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
    
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
        self::assertEquals('USD', $data[0]['currency']);
        self::assertEquals('EUR', $data[1]['currency']);

        $em->clear();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $em->createQuery('DELETE FROM ' . Client::class)->execute();
        $em->createQuery('DELETE FROM ' . Account::class)->execute();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
