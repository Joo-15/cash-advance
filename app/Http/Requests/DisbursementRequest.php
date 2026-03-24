<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DisbursementRequest extends FormRequest
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
                'cash_advance_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999'
                ],
                'disbursed_at' => [
                    'required',
                    'date_format:Y-m-d H:i:s',  // Format timestamp: 2026-03-23 14:30:00
                    'before_or_equal:now'        // Sebelum atau sama dengan waktu sekarang
                ]
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cash_advance_id.required' => 'id cash advance tidak ada',

            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah minimal 0',
            'amount.max' => 'Jumlah terlalu besar',

            'disbursed_at.required' => 'Tanggal pencairan wajib diisi.',
            'disbursed_at.date_format' => 'Format tanggal pencairan harus YYYY-MM-DD HH:MM:SS.',
            'disbursed_at.before_or_equal' => 'Tanggal pencairan tidak boleh melebihi waktu sekarang.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // dd($this->request_date);

        // Konversi timestamp milisecond ke format datetime
        if ($this->has('disbursed_at') && is_numeric($this->disbursed_at)) {
            $timestamp = intval($this->disbursed_at / 1000);
            $this->merge([
                'disbursed_at' => Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s')
            ]);
        }
        // Bersihkan input
        $this->merge([
            'amount' => str_replace(['.', ','], '', $this->amount) // Hapus pemisah ribuan
        ]);
    }
}
