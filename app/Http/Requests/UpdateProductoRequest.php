<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
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
        $producto = $this->route('producto');
        $keys = Producto::nombresPredeterminadosKeys();
        $permitidosNombre = $keys;
        if ($producto && $producto->nombre !== '' && ! in_array($producto->nombre, $keys, true)) {
            $permitidosNombre[] = $producto->nombre;
        }

        return [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:180', Rule::in($permitidosNombre)],
            'descripcion' => ['nullable', 'string', 'max:10000'],
            'unidad' => ['required', 'string', Rule::in(Producto::$unidades)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'categoria_id' => 'categoría',
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'unidad' => 'unidad',
        ];
    }
}
