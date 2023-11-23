<?php

namespace Acidwave\LaravelSSO\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckAuthRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'status' => ['required', 'string'],
            'bearer' => ['sometimes', 'nullable', 'string'],
            'hash' => ['required', 'string']
        ];
    }
}
