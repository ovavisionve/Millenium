<?php

namespace App\Http\Requests;

use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        foreach (['monto_bs', 'referencia', 'banco_destino', 'notas'] as $key) {
            if ($this->has($key) && $this->string($key)->trim()->isEmpty()) {
                $this->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tiposTasa = array_keys(Pago::tiposTasa());
        $metodos = array_keys(Pago::metodosPago());

        return [
            'fecha_recibo' => ['required', 'date'],
            'monto_aplicado_usd' => ['required', 'numeric', 'min:0.01'],
            'tipo_tasa' => ['required', Rule::in($tiposTasa)],
            'valor_tasa' => ['required', 'numeric', 'min:0.0001'],
            'monto_bs' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['required', Rule::in($metodos)],
            'referencia' => ['nullable', 'string', 'max:255'],
            'banco_destino' => ['nullable', 'string', 'max:100'],
            'comprobante' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Factura|null $factura */
            $factura = $this->route('factura');
            if (! $factura) {
                return;
            }
            $factura->refresh();
            if ((float) $factura->saldo_pendiente <= 0) {
                $validator->errors()->add('monto_aplicado_usd', 'Esta factura no tiene saldo pendiente.');

                return;
            }
            $monto = (float) $this->input('monto_aplicado_usd');
            if ($monto > (float) $factura->saldo_pendiente + 0.009) {
                $validator->errors()->add('monto_aplicado_usd', 'El monto no puede superar el saldo pendiente (USD '.number_format((float) $factura->saldo_pendiente, 2).').');
            }
        });
    }
}
