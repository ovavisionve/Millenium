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
        $this->merge([
            'sin_comprobante' => $this->boolean('sin_comprobante'),
        ]);

        foreach (['monto_bs', 'referencia', 'banco_destino', 'notas', 'recibido_por'] as $key) {
            if ($this->has($key) && $this->string($key)->trim()->isEmpty()) {
                $this->merge([$key => null]);
            }
        }

        $metodo = $this->string('metodo_pago')->toString();
        $grupo = $metodo !== '' ? Pago::grupoMetodo($metodo) : '';

        if ($grupo === 'pago_movil') {
            return;
        }

        if (! $this->filled('tipo_tasa')) {
            $this->merge(['tipo_tasa' => Pago::TIPO_TASA_BCV]);
        }
        if (! $this->filled('valor_tasa')) {
            $this->merge(['valor_tasa' => config('millennium.cobranza_tasa_placeholder_divisa', 1)]);
        }

    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tiposTasa = array_keys(Pago::tiposTasa());
        $metodos = array_keys(Pago::metodosPago());
        $metodo = $this->string('metodo_pago')->toString();
        $grupo = $metodo !== '' ? Pago::grupoMetodo($metodo) : '';
        $sinComprobante = $this->boolean('sin_comprobante');

        $rules = [
            'abonos' => ['required', 'array', 'min:1'],
            'abonos.*' => ['nullable', 'numeric', 'min:0'],
            'fecha_recibo' => ['required', 'date'],
            'metodo_pago' => ['required', Rule::in($metodos)],
            'notas' => ['nullable', 'string', 'max:2000'],
            'sin_comprobante' => ['boolean'],
        ];

        if ($grupo === 'pago_movil') {
            $rules['tipo_tasa'] = ['required', Rule::in($tiposTasa)];
            $rules['valor_tasa'] = ['required', 'numeric', 'min:0.0001'];
            $rules['monto_bs'] = ['required', 'numeric', 'min:0.01'];
            $rules['referencia'] = ['required', 'string', 'max:255'];
            $rules['banco_destino'] = ['required', 'string', 'max:100'];
            $rules['recibido_por'] = ['nullable', 'string', 'max:255'];
            $rules['comprobante'] = ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf'];
        } elseif ($grupo === 'efectivo') {
            $rules['tipo_tasa'] = ['required', Rule::in($tiposTasa)];
            $rules['valor_tasa'] = ['required', 'numeric', 'min:0.0001'];
            $rules['monto_bs'] = ['nullable', 'numeric', 'min:0'];
            $rules['recibido_por'] = ['required', 'string', 'max:255'];
            $rules['referencia'] = ['nullable', 'string', 'max:255'];
            $rules['banco_destino'] = ['nullable', 'string', 'max:100'];
            $rules['comprobante'] = $sinComprobante
                ? ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf']
                : ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf'];
        } else {
            // transferencia + usdt
            $rules['tipo_tasa'] = ['required', Rule::in($tiposTasa)];
            $rules['valor_tasa'] = ['required', 'numeric', 'min:0.0001'];
            $rules['monto_bs'] = ['nullable', 'numeric', 'min:0'];
            $rules['referencia'] = ['required', 'string', 'max:255'];
            $rules['banco_destino'] = ['nullable', 'string', 'max:100'];
            $rules['recibido_por'] = ['nullable', 'string', 'max:255'];
            $rules['comprobante'] = $sinComprobante
                ? ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf']
                : ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf'];
        }

        return $rules;
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
            $sumaUsd = 0.0;
            foreach ($abonos as $facturaId => $raw) {
                $monto = round((float) ($raw ?? 0), 2);
                if ($monto > 0) {
                    $hayPositivo = true;
                    $sumaUsd += $monto;
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

            $metodo = $this->string('metodo_pago')->toString();
            if (in_array($metodo, [Pago::METODO_PAGO_MOVIL, Pago::METODO_TRANSFERENCIA], true)) {
                $tasa = (float) $this->input('valor_tasa');
                $bs = (float) $this->input('monto_bs');
                if ($tasa > 0 && $bs > 0) {
                    $equiv = round($bs / $tasa, 2);
                    // Permitir sobrantes (p. ej. el cliente pagó de más en Bs),
                    // pero evitar que la suma de abonos en USD sea mayor al equivalente Bs/tasa.
                    if ($equiv + 0.02 < round($sumaUsd, 2)) {
                        $validator->errors()->add(
                            'monto_bs',
                            sprintf(
                                'El equivalente Bs/tasa (%s USD) es menor a la suma de abonos USD (%s). Ajustá abonos, Bs o tasa.',
                                number_format($equiv, 2),
                                number_format($sumaUsd, 2),
                            )
                        );
                    }
                }
            }
        });
    }
}
