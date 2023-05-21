<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Pesel extends Constraint
{
    public $message = 'Numer PESEL "{{ value }}" jest niepoprawny.';

    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
