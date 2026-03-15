<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

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
                'tanggal' => [
                    'required',
                    'date',
                    'before_or_equal:today'
                ],
                'keperluan' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255'
                ],
                'jumlah' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999'
                ],
                'keterangan' => [
                    'nullable',
                    'string',
                ]
            ];
        } else {
            // Update 
            $rules = [
                'tanggal' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today'
                ],
                'keperluan' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:3',
                    'max:255'
                ],
                'jumlah' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999'
                ],
                'keterangan' => [
                    'sometimes',
                    'nullable',
                    'string',
                ]
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini',

            'keperluan.required' => 'Keperluan harus diisi',
            'keperluan.min' => 'Keperluan minimal 3 karakter',
            'keperluan.max' => 'Keperluan maksimal 255 karakter',

            'jumlah.required' => 'Jumlah harus diisi',
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah minimal 0',
            'jumlah.max' => 'Jumlah terlalu besar',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Konversi tanggal jika dari frontend (timestamp milidetik)
        if (!empty($this->tanggal) && is_numeric($this->tanggal)) {
            $converted = Carbon::createFromTimestampMs($this->tanggal)
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d');

            $this->merge(['tanggal' => $converted]);
        }

        // Bersihkan input
        $this->merge([
            'keperluan' => trim($this->keperluan),
            'jumlah' => str_replace(['.', ','], '', $this->jumlah) // Hapus pemisah ribuan
        ]);
    }
}
