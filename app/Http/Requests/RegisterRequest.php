<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'min:4',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Check if password is all digits
                    if (!ctype_digit($value)) {
                        $fail('The password must contain only digits.');
                        return;
                    }
                    
                    // Check for sequential patterns (1234, 4321, etc.) only if length is 4
                    if (strlen($value) === 4) {
                        $digits = str_split($value);
                        $isSequential = true;
                        $isReverseSequential = true;
                        
                        for ($i = 1; $i < count($digits); $i++) {
                            // Check forward sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] + 1) {
                                $isSequential = false;
                            }
                            // Check reverse sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] - 1) {
                                $isReverseSequential = false;
                            }
                        }
                        
                        if ($isSequential || $isReverseSequential) {
                            $fail('The password cannot be a sequence like 1234 or 4321.');
                        }
                    }
                },
            ],
        ];
    }
}
