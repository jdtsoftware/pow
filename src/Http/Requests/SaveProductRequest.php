<?php

namespace JDT\Pow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'price' => 'numeric',
            'tokens' => 'integer',
            'wallet_token_type_id' => 'integer|exists:wallet_token_type,id',
            'criteria' => 'nullable|string',
            'adjustment' => 'nullable|string',

            'shop.*.id' => 'nullable|integer|exists:product_shop,id',
            'shop.*.name' => 'string',
            'shop.*.description' => 'string',
            'shop.*.quantity' => 'integer',
        ];
    }
}
