<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'role' => ['sometimes', 'in:client,performer'],
            'specialization' => ['required_if:role,performer', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Имя должно содержать минимум 2 символа.',
            'name.max' => 'Имя не должно превышать 50 символов.',
            'specialization.required_if' => 'Специализация обязательна для исполнителей.',
            'specialization.max' => 'Специализация не должна превышать 255 символов.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password.regex' => 'Пароль должен содержать хотя бы одну заглавную букву, одну строчную букву и одну цифру.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('role')) {
            $this->merge(['role' => 'client']);
        }
    }
}
