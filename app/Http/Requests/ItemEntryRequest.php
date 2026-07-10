<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'super_admin'], true);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $imageRules = $this->isMethod('post')
            ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
            : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return [
            'lart_number' => ['required', 'string', 'max:255'],
            'client_business_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'image' => $imageRules,
            'darjan' => ['required', 'integer', 'min:0'],
            'pieces' => ['required', 'array', 'min:1'],
            'pieces.*.name' => ['required', 'string', 'max:255'],
            'pieces.*.total_pieces' => ['required', 'integer', 'min:1'],
            'pieces.*.colors' => ['required', 'array', 'min:1'],
            'pieces.*.colors.*.type' => ['required', 'string', 'max:255'],
            'pieces.*.colors.*.rate' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'pieces.*.colors.*.type_color_count' => ['required', 'integer', 'min:1'],
            'pieces.*.sizes' => ['required', 'array', 'min:1'],
            'pieces.*.sizes.*.size' => ['required', 'string', 'max:255'],
            'pieces.*.sizes.*.percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
