<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PeselValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // Sprawdź, czy numer PESEL jest poprawnej długości
        if (strlen($value) != 11) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Sprawdź, czy każdy znak jest cyfrą
        if (!ctype_digit($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Sprawdź, czy data urodzenia jest poprawna
        $year = substr($value, 0, 2);
        $month = substr($value, 2, 2);
        $day = substr($value, 4, 2);

        if ($month > 80 && $month < 93) {
            $year += 1800;
            $month -= 80;
        } elseif ($month > 0 && $month < 13) {
            $year += 1900;
        } elseif ($month > 20 && $month < 33) {
            $year += 2000;
            $month -= 20;
        } elseif ($month > 40 && $month < 53) {
            $year += 2100;
            $month -= 40;
        } elseif ($month > 60 && $month < 73) {
            $year += 2200;
            $month -= 60;
        } else {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        if (!checkdate($month, $day, $year)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Sprawdź, czy cyfra kontrolna jest poprawna
        $weights = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3);
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * $value[$i];
        }
        $checksum = (10 - $sum % 10) % 10;

        if ($checksum != $value[10]) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }
    }
}
