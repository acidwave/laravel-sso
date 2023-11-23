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
        
        return [
            'broker' => ['required', 'string', 'exists:App\Models\Broker,name'],
            'token' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'return_url' => ['sometimes', 'url'],
            'command' => ['required', 'string']
        ];
    }
}
