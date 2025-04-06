<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotEqualAccounts extends Constraint
{
    public $message = 'The "from_account" and "to_account" cannot be the same.';
}