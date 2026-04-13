<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Support\VenezuelanDocumento;
use App\Support\VenezuelanTelefonoMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Millennium — edición de cliente: mismas reglas que el alta, ignorando el registro actual en unique.
 */
class UpdateClienteRequest extends FormRequest
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
        $num = VenezuelanDocumento::soloDigitos((string) $this->input('documento_numero', ''));
        $merge = [
            'tipo_documento' => $tipo,
            'documento_numero' => $num,
        ];
        $tel = $this->input('telefono');
        if ($tel === null || trim((string) $tel) === '') {
            $merge['telefono'] = null;
        } else {
            $merge['telefono'] = VenezuelanTelefonoMovil::soloDigitos((string) $tel);
        }
        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'tipo_documento' => ['required', 'string', 'size:1', Rule::in(Cliente::TIPOS_DOCUMENTO)],
            'documento_numero' => [
                'required',
                'string',
                'max:16',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $msg = VenezuelanDocumento::validarFormato(
                        (string) $this->input('tipo_documento'),
                        (string) $value
                    );
                    if ($msg !== null) {
                        $fail($msg);
                    }
                },
                Rule::unique('clientes', 'documento_numero')
                    ->where(fn ($q) => $q->where('tipo_documento', $this->input('tipo_documento')))
                    ->ignore($cliente->id),
            ],
            'nombre_razon_social' => ['required', 'string', 'min:3', 'max:180', 'regex:/^[\p{L}\p{N}][\p{L}\p{N}\s.\'\-,&]+$/u'],
            'telefono' => [
                'nullable',
                'string',
                'max:'.VenezuelanTelefonoMovil::LONGITUD,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $msg = VenezuelanTelefonoMovil::validarOpcional($value !== null ? (string) $value : null);
                    if ($msg !== null) {
                        $fail($msg);
                    }
                },
            ],
            'zona' => ['required', 'string', 'min:2', 'max:120'],
            'vendedor_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'documento_numero.unique' => __('clientes.documento_ya_registrado'),
            'nombre_razon_social.min' => 'El nombre o razón social debe tener al menos :min caracteres (evitá iniciales sueltas).',
            'nombre_razon_social.regex' => 'El nombre solo puede incluir letras, números y signos habituales (punto, coma, guion); no uses caracteres raros al inicio.',
            'zona.min' => 'Indicá la zona o ruta con al menos :min caracteres.',
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
