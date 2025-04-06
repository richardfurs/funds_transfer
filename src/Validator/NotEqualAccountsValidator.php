<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotEqualAccountsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotEqualAccounts) {
            throw new UnexpectedTypeException($constraint, NotEqualAccounts::class);
        }

        $transaction = $this->context->getObject();

        if ($transaction->getFromAccount()->getId() === $transaction->getToAccount()->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
