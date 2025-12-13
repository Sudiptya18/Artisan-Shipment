<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        // Check for master login credentials
        $isMasterLogin = ($this->input('username') === '1' || $this->input('email') === '1') 
            && $this->input('password') === '123';

        return [
            'email' => ['required_without:username', 'nullable', 'email'],
            'username' => ['required_without:email', 'nullable', 'string'],
            'password' => [
                'required',
                'string',
                $isMasterLogin ? 'min:3' : 'min:4', // Allow 3 chars for master login
                function ($attribute, $value, $fail) use ($isMasterLogin) {
                    // Skip validation for master login
                    if ($isMasterLogin) {
                        return;
                    }
                    
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
            'remember' => ['sometimes', 'boolean'],
        ];
    }
}
