<?php

namespace DouglasResende\ReceitaFederal\Request;

use Illuminate\Support\Facades\Input;

class CNPJRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cnpj' => 'required|cnpj',
            'captcha' => 'required'
        ];
    }

    public function all()
    {
        $input = parent::all();

        if (!empty(Input::get('cnpj'))) {
            $input['cnpj'] = $this->sanitizeNumbers($input['cnpj']);
        }

        return $input;
    }
}
