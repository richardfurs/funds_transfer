<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Transaction;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Rate;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class AccountController extends AbstractController
{
    public function __construct(private HttpClientInterface $client)
    {}

    #[Route('/accounts/{id}/transactions', name: 'transactions')]
    public function transactions(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $offset = $request->query->get('offset', 0);
        $limit = $request->query->get('limit', 10);

        $transactions = $entityManager->createQueryBuilder()
            ->select('t', 'from_account', 'to_account')
            ->from(Transaction::class, 't')
            ->leftJoin('t.from_account', 'from_account')
            ->leftJoin('t.to_account', 'to_account')
            ->where('t.from_account = :id OR t.to_account = :id')
            ->setParameter('id', $id)
            ->orderBy('t.created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit) 
            ->getQuery()
            ->getArrayResult();
 
        return $this->json($transactions);
    }

    #[Route('/accounts/transfer', name: 'transfer', methods: ['POST'])]
    public function transfer(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $post = $request->request->all();

        $baseAccount = $entityManager->getRepository(Account::class)->find($post['from_account_id']);
        $targetAccount = $entityManager->getRepository(Account::class)->find($post['to_account_id']);
        $baseCurrency = $baseAccount->getCurrency();
        $exchangeRate = 1;

        if ($baseCurrency !== $post['currency']) {
            $url = "https://api.frankfurter.app/latest?from={$post['currency']}&to={$baseCurrency}";
            $response = $this->client->request('GET', $url);
            $statusCode = $response->getStatusCode();
    
            if ($statusCode >= 300) {
                $rate = $entityManager->getRepository(Rate::class)
                    ->createQueryBuilder('r')
                    ->where('r.base_currency = :baseCurrency')
                    ->andWhere('r.target_currency = :targetCurrency')
                    ->setParameter('baseCurrency', $baseCurrency)
                    ->setParameter('targetCurrency', $post['currency'])
                    ->orderBy('r.created_at', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    
                $exchangeRate = $rate->getRate();
            } else {
                $data = $response->toArray();
                $exchangeRate = $data['rates'][$baseCurrency];
            }
        }

        $transaction = new Transaction();
        $transaction->setFromAccount($baseAccount);
        $transaction->setToAccount($targetAccount);
        $transaction->setCurrency($post['currency']);
        $transaction->setAmount($post['amount']);
        $transaction->setExchangeRate($exchangeRate);

        $cache = new FilesystemAdapter();
        $exchangeRatesCache = $cache->getItem('exchange_rate');
        $exchangeRatesCache->set($exchangeRate);
        $cache->save($exchangeRatesCache);

        $errors = $validator->validate($transaction);

        $cache->deleteItem('exchange_rate');

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
    
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($transaction);

        $baseBalance = (float) $baseAccount->getBalance();
        $targetBalance = (float) $targetAccount->getBalance();

        $sentBaseValue = $post['amount'] * $exchangeRate;
        $newBaseAccountBalance = $baseBalance - $sentBaseValue;

        $newTargetAccountBalance = $targetBalance + $post['amount'];

        $baseAccount->setBalance($newBaseAccountBalance);
        $targetAccount->setBalance($newTargetAccountBalance);

        $entityManager->persist($baseAccount);
        $entityManager->persist($targetAccount);

        $entityManager->flush();

        return $this->json('Transaction successful');
    }
}
