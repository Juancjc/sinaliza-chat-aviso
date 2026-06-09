<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMensagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sendMessage', $this->route('grupo'));
    }

    public function rules(): array
    {
        return [
            'mensagem' => ['required', 'string', 'max:5000'],
        ];
    }
}
