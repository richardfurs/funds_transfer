<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InsufficientFunds extends Constraint
{
    public $message = 'Insufficient funds, remaining balance {{ balance }} {{ currency }}, requested sum {{ requestedSum }} {{ currency }}';
}