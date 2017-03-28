<?php

namespace DouglasResende\ReceitaFederal\Request;

use Illuminate\Support\Facades\Input;

class CPFRequest extends Request
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
            'cpf' => 'required|numeric|digits:11',
            'birthday' => 'required|date|date_format:d/m/Y',
            'captcha' => 'required'
        ];
    }

    public function all()
    {
        $input = parent::all();

        if (!empty(Input::get('cpf'))) {
            $input['cpf'] = $this->sanitizeNumbers($input['cpf']);
        }

        return $input;
    }
}
