<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
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
            'categoria_id' => ['required', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:180', Rule::in(Producto::nombresPredeterminadosKeys())],
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
