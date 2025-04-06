<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotCompatibleCurrency extends Constraint
{
    public $message = 'Not compatible currency, need to be {{ requiredCurrency }} instead of {{ usedCurrency }}';
}