<?php

namespace App\Http\Requests;

use App\Enums\DealStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DealUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->getRole() === 'client';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', Rule::enum(DealStatus::class)],
            'performer_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
