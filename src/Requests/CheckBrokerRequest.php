<?php

namespace Acidwave\LaravelSSO\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckBrokerRequest extends FormRequest
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
        $config = $this->getConfig('laravel-sso');
        return [
            'broker' => ['required', 'string', "exists:{$config['brokersModel']},name"],
            'token' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'return_url' => ['sometimes', 'url'],
            'command' => ['required', 'string']
        ];
    }
}
