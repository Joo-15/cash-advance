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

            $rules = [
                'step_order' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                    'unique:approval_steps,step_order'
                ],


            ];
        } else {
            // Update 
            $rules = [
                'step_order' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                    'unique:approval_steps,step_order'

                ],

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
            'step_order.unique' => 'Urutan langkah sudah digunakan.',
        ];
    }

    protected function prepareForValidation(): void
    {

        //
    }
}
