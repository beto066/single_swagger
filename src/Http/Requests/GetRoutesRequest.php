<?php

namespace SingleSoftware\SinglesSwagger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRoutesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'route_directory' => 'nullable|string',
        ];
    }
}
