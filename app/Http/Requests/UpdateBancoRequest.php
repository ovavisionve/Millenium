<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBancoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bancoId = $this->route('banco')?->id;

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('bancos', 'nombre')->ignore($bancoId)],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'activo' => ['boolean'],
        ];
    }
}

