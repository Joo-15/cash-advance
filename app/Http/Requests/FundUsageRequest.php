<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundUsageRequest extends FormRequest
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
                'files.*' => [
                    'required',
                    'mimes:pdf',
                    'max:2048'
                ],
            ];
        } else {
            // Update 
            $rules = [
                'files.*' => [
                    'sometimes',
                    'required',
                    'mimes:pdf',
                    'max:2048'
                ],
            ];
        }

        return $rules;
    }
}
