<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;

final class ClientController extends AbstractController
{
    #[Route('/clients/{id}/accounts', name: 'account')]
    public function accounts(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $accounts = $entityManager->createQueryBuilder()
            ->select('a')
            ->from(Account::class, 'a')
            ->leftJoin('a.client', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();

        return $this->json($accounts);
    }
}
