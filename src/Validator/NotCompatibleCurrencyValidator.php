<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotCompatibleCurrencyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotCompatibleCurrency) {
            throw new UnexpectedTypeException($constraint, NotCompatibleCurrency::class);
        }

        $transaction = $this->context->getObject();

        if ($value !== $transaction->getToAccount()->getCurrency()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ requiredCurrency }}', $transaction->getToAccount()->getCurrency())
                ->setParameter('{{ usedCurrency }}', $value)
                ->addViolation();
        }
    }
}
