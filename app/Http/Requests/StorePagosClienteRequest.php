<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagosClienteRequest extends FormRequest
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
            'abonos' => ['required', 'array', 'min:1'],
            'abonos.*' => ['nullable', 'numeric', 'min:0'],
            'fecha_recibo' => ['required', 'date'],
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
            /** @var Cliente|null $cliente */
            $cliente = $this->route('cliente');
            if (! $cliente) {
                return;
            }

            $abonos = $this->input('abonos', []);
            $hayPositivo = false;
            foreach ($abonos as $facturaId => $raw) {
                $monto = round((float) ($raw ?? 0), 2);
                if ($monto > 0) {
                    $hayPositivo = true;
                    break;
                }
            }
            if (! $hayPositivo) {
                $validator->errors()->add('abonos', 'Indica al menos un monto a abonar mayor que cero.');

                return;
            }

            foreach ($abonos as $facturaId => $raw) {
                $monto = round((float) ($raw ?? 0), 2);
                if ($monto <= 0) {
                    continue;
                }

                $factura = Factura::query()
                    ->where('cliente_id', $cliente->id)
                    ->whereKey($facturaId)
                    ->first();

                if (! $factura) {
                    $validator->errors()->add('abonos', 'Factura no válida para este cliente.');

                    return;
                }

                if ($monto > (float) $factura->saldo_pendiente + 0.009) {
                    $validator->errors()->add(
                        'abonos.'.$facturaId,
                        sprintf(
                            'El monto no puede superar el saldo (USD %s).',
                            number_format((float) $factura->saldo_pendiente, 2)
                        )
                    );
                }
            }
        });
    }
}
