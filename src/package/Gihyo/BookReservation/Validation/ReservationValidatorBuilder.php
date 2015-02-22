<?php
namespace Gihyo\BookReservation\Validation;

use Shin1x1\ValidatorBuilder\ValidatorBuilder;
use Validator;

class ReservationValidorBuilder implements ValidatorBuilder
{
    /**
     * @param array $inputs
     * @return \Illuminate\Validation\Validator
     */
    public function create(array $inputs)
    {
        return Validator::make(
            $inputs,
            [
                'asin' => 'required|regex:/\A[0-9a-zA-z]+\z/',
                'quantity' => 'required|numeric',
            ]
        );
    }
}