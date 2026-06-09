<?php

namespace App\Http\Requests;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;

class StoreGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Grupo::class);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
