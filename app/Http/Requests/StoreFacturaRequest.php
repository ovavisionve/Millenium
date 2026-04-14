<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
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
                $producto = Producto::query()->find($pid);
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
            'fecha_emision' => 'fecha de emisión',
            'dias_credito' => 'días de crédito',
            'lineas' => 'líneas',
        ];
    }
}
