<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Client;
use App\Entity\Account;
use App\Entity\Transaction;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class AccountControllerTest extends WebTestCase
{
    public function testTransactions(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        
        $testClient = new Client();
        $testClient->setName('Test Client');
        $testClient->setEmail('test@mail.com');

        $account = new Account();
        $account->setCurrency('USD')->setBalance(100)->setClient($testClient);
        
        $em->persist($testClient);
        $em->persist($account);

        $transaction1 = new Transaction();
        $transaction1->setAmount(50);
        $transaction1->setFromAccount($account);
        $transaction1->setToAccount($account);
        $transaction1->setCurrency('USD');
        $transaction1->setExchangeRate(1);
        $transaction1->setCreatedAt(new \DateTimeImmutable('-1 day'));
        
        $transaction2 = new Transaction();
        $transaction2->setAmount(30);
        $transaction2->setFromAccount($account);
        $transaction2->setToAccount($account);
        $transaction2->setCurrency('USD');
        $transaction2->setExchangeRate(1);
        $transaction2->setCreatedAt(new \DateTimeImmutable('-2 day'));

        $em->persist($transaction1);
        $em->persist($transaction2);
        $em->flush();

        $client->request('GET', '/accounts/' . $account->getId() . '/transactions', [
            'offset' => 0,
            'limit' => 10,
        ]);

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        self::assertCount(2, $data);

        self::assertEquals(50, $data[0]['amount']);
        self::assertEquals(30, $data[1]['amount']);

        $client->request('GET', '/accounts/' . $account->getId() . '/transactions', [
            'offset' => 1,
            'limit' => 1,
        ]);

        self::assertResponseIsSuccessful();
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        self::assertCount(1, $data);

        self::assertEquals(30, $data[0]['amount']);

        $em->clear();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $em->createQuery('DELETE FROM ' . Client::class)->execute();
        $em->createQuery('DELETE FROM ' . Account::class)->execute();
        $em->createQuery('DELETE FROM ' . Transaction::class)->execute();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testTransfer(): void
    {
        $client = static::createClient();
        
        $container = static::getContainer();
        $httpClient = self::createMock(HttpClientInterface::class);

        $mockedResponse = self::createMock(ResponseInterface::class);
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('toArray')->willReturn([
            'rates' => ['USD' => 1.2]
        ]);

        $httpClient->method('request')->willReturn($mockedResponse);

        $container->set(HttpClientInterface::class, $httpClient);

        $em = $container->get(EntityManagerInterface::class);

        $testClient = new Client();
        $testClient->setName('Test Client')->setEmail('test@mail.com');
        $em->persist($testClient);
        
        $account1 = new Account();
        $account1->setCurrency('USD')->setBalance(100)->setClient($testClient);
        $em->persist($account1);
        
        $account2 = new Account();
        $account2->setCurrency('EUR')->setBalance(200)->setClient($testClient);
        $em->persist($account2);

        $em->flush();

        $client->request('POST', '/accounts/transfer', [
            'from_account_id' => $account1->getId(),
            'to_account_id' => $account2->getId(),
            'amount' => 50,
            'currency' => 'EUR',
        ]);

        $response = $client->getResponse();
        
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        self::assertStringContainsString('Transaction successful', $response->getContent());

        $em->clear();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $em->createQuery('DELETE FROM ' . Client::class)->execute();
        $em->createQuery('DELETE FROM ' . Account::class)->execute();
        $em->createQuery('DELETE FROM ' . Transaction::class)->execute();
        $em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
