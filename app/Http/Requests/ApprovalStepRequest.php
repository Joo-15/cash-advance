<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalStepRequest extends FormRequest
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
        // create
        if ($this->isMethod('post')) {
            // Store - required
            $rules = [
                'role_id' => [
                    'required',
                    'integer',
                    'exists:roles,id'
                ],
                'step_order' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],

            ];
        } else {
            // Update 
            $rules = [
                'role_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    'exists:roles,id'
                ],
                'step_order' => [
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
            'role_id.required' => 'Role harus dipilih.',
            'role_id.integer' => 'Role harus berupa angka yang valid.',
            'role_id.exists' => 'Role yang dipilih tidak tersedia dalam sistem.',

            // Messages untuk step_order
            'step_order.required' => 'Urutan langkah harus diisi.',
            'step_order.numeric' => 'Urutan langkah harus berupa angka.',
            'step_order.min' => 'Urutan langkah minimal :min.',
            'step_order.max' => 'Urutan langkah maksimal :max.',
        ];
    }

    protected function prepareForValidation(): void
    {

        //
    }
}
