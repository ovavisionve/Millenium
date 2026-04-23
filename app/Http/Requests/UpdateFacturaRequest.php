<?php

namespace App\Http\Requests;

use App\Models\Categoria;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $lineas = $this->input('lineas', []);
        foreach ($lineas as $i => $row) {
            $ca = $row['cantidad_animales'] ?? null;
            if ($ca === '' || $ca === null) {
                $lineas[$i]['cantidad_animales'] = null;
            }
        }

        $vid = $this->input('vendedor_id');
        $mpp = $this->input('metodo_pago_previsto');
        $obs = trim((string) $this->input('observaciones', ''));
        $this->merge([
            'numero_factura' => trim((string) $this->input('numero_factura', '')),
            'lineas' => $lineas,
            'vendedor_id' => ($vid !== null && $vid !== '') ? (int) $vid : null,
            'metodo_pago_previsto' => ($mpp !== null && $mpp !== '') ? $mpp : null,
            'observaciones' => $obs !== '' ? $obs : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $factura = $this->route('factura');

        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'vendedor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('is_active', true)->whereIn('role', [
                    User::ROLE_ADMIN,
                    User::ROLE_VERIFICADOR,
                    User::ROLE_VENDEDOR_GENERAL,
                    User::ROLE_VENDEDOR_NORMAL,
                ])),
            ],
            'numero_factura' => [
                'required',
                'string',
                'max:64',
                Rule::unique('facturas', 'numero_factura')->ignore($factura->getKey()),
            ],
            'fecha_emision' => ['required', 'date'],
            'dias_credito' => ['required', 'integer', 'min:0', 'max:3650'],
            'metodo_pago_previsto' => ['nullable', 'string', 'max:30', Rule::in(array_keys(Pago::metodosPago()))],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.categoria_id' => ['required', 'exists:categorias,id'],
            'lineas.*.cantidad_animales' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'lineas.*.cantidad' => ['required', 'numeric', 'min:0.001'],
            'lineas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            foreach ($this->input('lineas', []) as $i => $linea) {
                $cid = $linea['categoria_id'] ?? null;
                if (! $cid) {
                    continue;
                }
                $categoria = Categoria::query()->find($cid);
                if ($categoria && ! $categoria->activo) {
                    $v->errors()->add("lineas.$i.categoria_id", 'La categoría seleccionada está inactiva.');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'vendedor_id' => 'vendedor',
            'numero_factura' => 'número de factura',
            'fecha_emision' => 'fecha de emisión',
            'dias_credito' => 'días de crédito',
            'metodo_pago_previsto' => 'forma de pago del cliente',
            'observaciones' => 'observaciones',
            'lineas' => 'líneas',
            'lineas.*.cantidad_animales' => 'cantidad de animales',
            'lineas.*.cantidad' => 'unidad/Kilos',
        ];
    }
}
