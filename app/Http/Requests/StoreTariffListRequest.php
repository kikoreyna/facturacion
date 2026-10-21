<?php

namespace App\Http\Requests;

use App\Support\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTariffListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('tariff_lists', 'name')->ignore($this->route('tariff_list'))],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'currencies.*' => ['nullable', Rule::in(Currency::codes())],
        ];
    }
}
