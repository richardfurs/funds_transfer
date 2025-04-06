<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class InsufficientFundsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof InsufficientFunds) {
            throw new UnexpectedTypeException($constraint, InsufficientFunds::class);
        }

        $transaction = $this->context->getObject();
        $cache = new FilesystemAdapter();
        $exchangeRate = $cache->getItem('exchange_rate')->get();
        $fromAccountCurrencyValue = $value * $exchangeRate;

        if ($transaction->getFromAccount()->getBalance() < $fromAccountCurrencyValue) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ balance }}', $transaction->getFromAccount()->getBalance())
                ->setParameter('{{ requestedSum }}', $fromAccountCurrencyValue)
                ->setParameter('{{ currency }}', $transaction->getFromAccount()->getCurrency())
                ->addViolation();
        }
    }
}
