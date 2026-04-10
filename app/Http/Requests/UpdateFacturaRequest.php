<?php

namespace App\Http\Requests;

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
        if ($this->has('numero_factura') && $this->string('numero_factura')->trim()->isEmpty()) {
            $this->merge(['numero_factura' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $factura = $this->route('factura');
        $id = $factura?->id;

        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'numero_factura' => ['nullable', 'string', 'max:64', Rule::unique('facturas', 'numero_factura')->ignore($id)],
            'fecha_emision' => ['required', 'date'],
            'dias_credito' => ['required', 'integer', 'min:0', 'max:3650'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'exists:productos,id'],
            'lineas.*.cantidad' => ['required', 'numeric', 'min:0.001'],
            'lineas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            foreach ($this->input('lineas', []) as $i => $linea) {
                $pid = $linea['producto_id'] ?? null;
                if (! $pid) {
                    continue;
                }
                $producto = \App\Models\Producto::query()->find($pid);
                if ($producto && ! $producto->activo) {
                    $v->errors()->add("lineas.$i.producto_id", 'El producto seleccionado está inactivo.');
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
            'numero_factura' => 'número de factura',
            'fecha_emision' => 'fecha de emisión',
            'dias_credito' => 'días de crédito',
            'lineas' => 'líneas',
        ];
    }
}
