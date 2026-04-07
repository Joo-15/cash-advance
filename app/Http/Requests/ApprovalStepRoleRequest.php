<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalStepRoleRequest extends FormRequest
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
        // create
        if ($this->isMethod('post')) {
            // Store - required
            $rules = [
                'approval_step_id' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],
                'role_id' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],


            ];
        } else {
            // Update 
            $rules = [
                'approval_step_id' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],
                'role_id' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],


            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'approval_step_id.required' => 'Urutan langkah harus diisi.',
            'approval_step_id.numeric' => 'Urutan langkah harus berupa angka.',
            'approval_step_id.min' => 'Urutan langkah minimal :min.',
            'approval_step_id.max' => 'Urutan langkah maksimal :max.',

            'role_id.required' => 'Role harus diisi.',
            'role_id.numeric' => 'Role langkah harus berupa angka.',
            'role_id.min' => 'Role langkah minimal :min.',
            'role_id.max' => 'Role langkah maksimal :max.',

        ];
    }
}
