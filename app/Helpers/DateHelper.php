<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format tanggal ke bahasa Indonesia
     */
    public static function formatIndonesian($date, $format = 'd F Y')
    {
        if (!$date) return '-';

        Carbon::setLocale('id');
        return Carbon::parse($date)->translatedFormat($format);
    }

    /**
     * Format dengan hari
     */
    public static function formatWithDay($date)
    {
        if (!$date) return '-';

        Carbon::setLocale('id');
        return Carbon::parse($date)->translatedFormat('l, d F Y');
    }

    /**
     * Format pendek
     */
    public static function formatShort($date)
    {
        if (!$date) return '-';

        Carbon::setLocale('id');
        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    /**
     * Format untuk filename
     */
    public static function formatFilename($date)
    {
        if (!$date) return 'now';

        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Nama bulan dalam bahasa Indonesia
     */
    public static function getMonthName($month)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $months[(int)$month] ?? '-';
    }

    /**
     * Nama hari dalam bahasa Indonesia
     */
    public static function getDayName($day)
    {
        $days = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        return $days[$day] ?? '-';
    }
}
