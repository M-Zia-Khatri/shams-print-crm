<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'entries' => ['required', 'array', 'min:1'],
                'entries.*.lart_number' => ['required', 'string', 'max:255'],
                'entries.*.client_business_name' => ['required', 'string', 'max:255'],
                'entries.*.description' => ['required', 'string', 'max:255'],
                'entries.*.size_description' => ['required', 'string', 'max:255'],
                'entries.*.darjan' => ['required', 'integer', 'min:0'],
                'entries.*.total_color' => ['required', 'integer', 'min:0'],
                'entries.*.total_rate' => ['required', 'numeric', 'min:0'],
                'entries.*.image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
                'entries.*.total_amount' => ['prohibited'],
            ];
        }

        return [
            'lart_number' => ['required', 'string', 'max:255'],
            'client_business_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'size_description' => ['required', 'string', 'max:255'],
            'darjan' => ['required', 'integer', 'min:0'],
            'total_color' => ['required', 'integer', 'min:0'],
            'total_rate' => ['required', 'numeric', 'min:0'],
            'image' => [($this->isMethod('put') || $this->isMethod('patch')) ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'total_amount' => ['prohibited'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function validatedEntries(): array
    {
        return $this->validated('entries', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedEntry(): array
    {
        return collect($this->validated())->except(['image'])->all();
    }
}
