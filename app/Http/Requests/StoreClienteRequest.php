<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Support\VenezuelanDocumento;
use App\Support\VenezuelanTelefonoMovil;
use App\Support\ZonasComerciales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Millennium — alta de cliente: documento venezolano normalizado (solo dígitos en BD),
 * reglas por tipo (V/E cédula, J/G RIF 9, P pasaporte 8–9), unicidad compuesta.
 */
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

        $email = $this->input('email');
        if ($email === null || trim((string) $email) === '') {
            $merge['email'] = null;
        } else {
            $merge['email'] = strtolower(trim((string) $email));
        }

        $direccion = $this->input('direccion');
        if ($direccion === null || trim((string) $direccion) === '') {
            $merge['direccion'] = null;
        } else {
            $merge['direccion'] = trim((string) $direccion);
        }

        foreach (['id_estado', 'id_ciudad', 'id_municipio', 'id_parroquia'] as $k) {
            $v = $this->input($k);
            if ($v === null || $v === '' || $v === '0') {
                $merge[$k] = null;
            }
        }

        $sel = trim((string) $this->input('zona_select', ''));
        if ($sel === '__otra__') {
            $ot = trim((string) $this->input('zona_otra', ''));
            $merge['zona'] = $ot === '' ? null : $ot;
        } elseif ($sel !== '') {
            $merge['zona'] = $sel;
        } else {
            $merge['zona'] = null;
        }

        $this->merge($merge);
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $sel = $this->string('zona_select')->toString();
            $codes = ZonasComerciales::codigos();
            if ($sel !== '' && $sel !== '__otra__' && ! in_array($sel, $codes, true)) {
                $validator->errors()->add('zona_select', 'Elegí una zona de la lista o «Otra».');
            }
            if ($sel === '__otra__') {
                $z = trim((string) $this->input('zona', ''));
                if ($z === '') {
                    $validator->errors()->add('zona_otra', 'Completá la ruta o sector cuando elegís «Otra».');
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_estado' => ['required', 'integer', 'exists:estados,id_estado'],
            'id_ciudad' => [
                'nullable',
                'integer',
                'exists:ciudades,id_ciudad',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $estado = (int) $this->input('id_estado');
                    $ok = DB::table('ciudades')
                        ->where('id_ciudad', (int) $value)
                        ->where('id_estado', $estado)
                        ->exists();
                    if (! $ok) {
                        $fail('La ciudad no corresponde al estado seleccionado.');
                    }
                },
            ],
            'id_municipio' => [
                'nullable',
                'integer',
                'exists:municipios,id_municipio',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $estado = (int) $this->input('id_estado');
                    $ok = DB::table('municipios')
                        ->where('id_municipio', (int) $value)
                        ->where('id_estado', $estado)
                        ->exists();
                    if (! $ok) {
                        $fail('El municipio no corresponde al estado seleccionado.');
                    }
                },
            ],
            'id_parroquia' => [
                'nullable',
                'integer',
                'exists:parroquias,id_parroquia',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $m = (int) $this->input('id_municipio');
                    $p = (int) $value;
                    if ($m <= 0) {
                        $fail('Para elegir parroquia primero indicá el municipio.');

                        return;
                    }
                    $ok = DB::table('parroquias')
                        ->where('id_parroquia', $p)
                        ->where('id_municipio', $m)
                        ->exists();
                    if (! $ok) {
                        $fail('La parroquia seleccionada no pertenece al municipio.');
                    }
                },
            ],
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
                Rule::unique('clientes', 'documento_numero')->where(
                    fn ($q) => $q->where('tipo_documento', $this->input('tipo_documento'))
                ),
            ],
            'email' => ['nullable', 'string', 'max:180', 'email'],
            'direccion' => ['nullable', 'string', 'max:255'],
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
            'zona' => [
                'nullable',
                'string',
                'max:120',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (in_array((string) $value, ZonasComerciales::codigos(), true)) {
                        return;
                    }
                    if (mb_strlen((string) $value) < 2) {
                        $fail('La ruta o sector debe tener al menos 2 caracteres.');
                    }
                },
            ],
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_estado' => 'estado',
            'id_ciudad' => 'ciudad',
            'id_municipio' => 'municipio',
            'id_parroquia' => 'parroquia',
            'tipo_documento' => 'tipo de documento',
            'documento_numero' => 'número de documento',
            'nombre_razon_social' => 'nombre o razón social',
            'email' => 'correo',
            'direccion' => 'dirección',
            'telefono' => 'teléfono',
            'zona' => 'zona comercial o ruta',
            'zona_otra' => 'ruta o sector (otra)',
            'vendedor_id' => 'vendedor',
        ];
    }
}
