<?php

namespace DouglasResende\ReceitaFederal\Request;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * @param $value
     * @return mixed
     */
    protected function sanitizeNumbers($value)
    {
        $value = preg_replace("/[^0-9]/", "", $value);
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        return $value;
    }
}