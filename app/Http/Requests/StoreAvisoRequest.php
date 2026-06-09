<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sendAviso', $this->route('grupo'));
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'mensagem' => ['required', 'string', 'max:10000'],
        ];
    }
}
