<?php

namespace Vera\DniRucCheckout\Model\Validator;

class RucValidator
{
    public function isValid(string $ruc): bool
    {
        if (!preg_match('/\A\d{11}\z/D', $ruc)
            || preg_match('/\A0+\z/D', $ruc)
            || !in_array(substr($ruc, 0, 2), ['10', '15', '17', '20'], true)
        ) {
            return false;
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += ((int) $ruc[$index]) * $weight;
        }

        $checkDigit = (11 - ($sum % 11)) % 10;

        return $checkDigit === (int) $ruc[10];
    }
}
