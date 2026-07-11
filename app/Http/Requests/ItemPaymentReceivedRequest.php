<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemPaymentReceivedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'party_name' => 'required|string',
            'received_amount' => 'required|numeric',
        ];
    }
}
