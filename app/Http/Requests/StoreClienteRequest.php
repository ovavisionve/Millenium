<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $tipo = strtoupper(substr((string) $this->input('tipo_documento', 'V'), 0, 1));
        if (! in_array($tipo, Cliente::TIPOS_DOCUMENTO, true)) {
            $tipo = 'V';
        }
        $num = trim((string) $this->input('documento_numero', ''));
        $this->merge([
            'tipo_documento' => $tipo,
            'documento_numero' => $num,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo_documento' => ['required', 'string', 'size:1', Rule::in(Cliente::TIPOS_DOCUMENTO)],
            'documento_numero' => [
                'required',
                'string',
                'max:32',
                'regex:/^[0-9A-Za-z.\-]+$/u',
                Rule::unique('clientes', 'documento_numero')->where(
                    fn ($q) => $q->where('tipo_documento', $this->input('tipo_documento'))
                ),
            ],
            'nombre_razon_social' => ['required', 'string', 'max:180'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'zona' => ['required', 'string', 'max:120'],
            'vendedor_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tipo_documento' => 'tipo de documento',
            'documento_numero' => 'número de documento',
            'nombre_razon_social' => 'nombre o razón social',
            'telefono' => 'teléfono',
            'zona' => 'zona o ruta',
            'vendedor_id' => 'vendedor',
        ];
    }
}
