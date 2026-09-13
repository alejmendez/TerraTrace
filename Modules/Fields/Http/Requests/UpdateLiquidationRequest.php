<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiquidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'date' => ['required', 'date'],
            'delivery_date' => ['required', 'date'],
            'reception_date' => ['required', 'date'],
            'weight_with_earth' => ['required', 'numeric'],
            'weight_washed' => ['required', 'numeric'],
            'dollar_value' => ['required', 'numeric'],
            'importer_id.value' => ['required', 'exists:importers,id'],
            'field_id' => 'required',
            'field_id.value' => 'required|exists:fields,id',
            'products' => ['required', 'array'],
            'products.*.id' => ['nullable', 'exists:liquidation_products,id'],
            'products.*.category_product_id' => ['required', 'exists:category_products,id'],
            'products.*.weight' => ['required', 'numeric'],
            'products.*.price' => ['required', 'numeric'],
        ];
    }

    public function attributes()
    {
        return [
            'batch_number' => 'batch n°',
            'delivery_date' => 'Fecha de entrega',
            'importer_id' => 'Importador',
            'field_id' => 'campo',
            'products' => 'productos',
        ];
    }
}
