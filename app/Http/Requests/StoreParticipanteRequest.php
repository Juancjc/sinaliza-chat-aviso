<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParticipanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageParticipants', $this->route('grupo'));
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('tipo_usuario', 'aluno'),
                Rule::unique('grupo_user', 'user_id')->where('grupo_id', $this->route('grupo')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Selecione um aluno válido.',
            'user_id.unique' => 'Este aluno já participa do grupo.',
        ];
    }
}
