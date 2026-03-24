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
                'step_order' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],
                'name' => ['required', 'string', 'min:3', 'max:255'],


            ];
        } else {
            // Update 
            $rules = [
                'step_order' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100'
                ],
                'name' => ['sometimes', 'required', 'string', 'min:3'],


            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'step_order.required' => 'Urutan langkah harus diisi.',
            'step_order.numeric' => 'Urutan langkah harus berupa angka.',
            'step_order.min' => 'Urutan langkah minimal :min.',
            'step_order.max' => 'Urutan langkah maksimal :max.',

            'name.required' => 'Username harus diisi',
            'name.min' => 'Username minimal 3 karakter',
        ];
    }

    protected function prepareForValidation(): void
    {

        //
    }
}
