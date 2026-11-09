<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'ends_with:.test'],
            'address' => ['required', 'string', 'min:5', 'max:240'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'postal_code' => ['required', 'string', 'min:2', 'max:20'],
            'checkout_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return ['email.ends_with' => 'Use a fictional .test email address, such as alex@example.test. This is a demo checkout.'];
    }
}
