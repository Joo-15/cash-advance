<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        // create
        if ($this->isMethod('post')) {
            // Store - required
            $rules = [
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'username' => ['required', 'string', 'min:3', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users'), 'max:255'],
                'password' => ['required', 'string', 'min:6', 'max:255'],
                'department_id' => [
                    'required',
                    'integer',
                    'exists:departments,id' // mengecek id referensi department
                ],
                'role_id' => [
                    'required',
                    'integer',
                    'exists:roles,id'
                ]

            ];
        } else {
            // Update 
            $rules = [
                'name' => ['sometimes', 'required', 'string', 'max:255', 'min:3'],
                'username' => ['sometimes', 'required', 'string', 'min:3'],
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                    'max:255'
                ],
                'password' => ['nullable', 'string', 'min:6', 'max:255'],
                'department_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    'exists:departments,id'
                ],
                'role_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    'exists:roles,id'
                ],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.min' => 'Nama minimal 3 karakter',
            'name.max' => 'Nama maksimal 255 karakter',

            'username.required' => 'Username harus diisi',
            'username.min' => 'Username minimal 3 karakter',

            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'email.max' => 'Email maksimal 255 karakter',

            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.max' => 'Password maksimal 255 karakter',

            'department_id.required' => 'Department harus dipilih',
            'department_id.exists' => 'Department tidak valid',

            'role_id.required' => 'Department harus dipilih',
            'role_id.exists' => 'Department tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }

        if ($this->has('username')) {
            $this->merge([
                'username' => trim($this->username)
            ]);
        }
    }
}
