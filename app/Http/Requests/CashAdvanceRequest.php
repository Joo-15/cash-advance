<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CashAdvanceRequest extends FormRequest
{

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
                'request_date' => [
                    'required',
                    'date',
                    'before_or_equal:today'
                ],
                'purpose' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255'
                ],
                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999'
                ]
            ];
        } else {
            // Update 
            $rules = [
                'request_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today'
                ],
                'purpose' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:3',
                    'max:255'
                ],
                'amount' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999'
                ]
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'request_date.required' => 'Tanggal harus diisi',
            'request_date.date' => 'Format tanggal tidak valid',
            'request_date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini',

            'purpose.required' => 'Keperluan harus diisi',
            'purpose.min' => 'Keperluan minimal 3 karakter',
            'purpose.max' => 'Keperluan maksimal 255 karakter',

            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah minimal 0',
            'amount.max' => 'Jumlah terlalu besar',
        ];
    }

    protected function prepareForValidation(): void
    {
        // dd($this->request_date);

        if (!empty($this->request_date) && is_numeric($this->request_date)) {
            // Debug: lihat nilai asli
            Log::info('Original timestamp', [
                'timestamp' => $this->request_date,
                'length' => strlen((string)$this->request_date)
            ]);

            // Jika timestamp sudah dalam detik (10 digit)
            if (strlen((string)$this->request_date) === 10) {
                $converted = Carbon::createFromTimestamp($this->request_date)
                    ->setTimezone('Asia/Jakarta')
                    ->format('Y-m-d');
            }
            // Jika timestamp dalam milidetik (13 digit)
            elseif (strlen((string)$this->request_date) === 13) {
                $converted = Carbon::createFromTimestamp($this->request_date / 1000)
                    ->setTimezone('Asia/Jakarta')
                    ->format('Y-m-d');
            }
            // Jika timestamp dalam format lain
            else {
                $converted = null;
            }

            $this->merge(['request_date' => $converted]);
        }

        // Bersihkan input
        $this->merge([
            'keperluan' => trim($this->purpose),
            'jumlah' => str_replace(['.', ','], '', $this->amount) // Hapus pemisah ribuan
        ]);
    }
}
