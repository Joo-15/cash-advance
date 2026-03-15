<?php
// app/Traits/HasFilters.php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasFilters
{
    protected function getFilters(Request $request): array
    {
        return [
            'search' => $request->input('search', ''),
            'status' => $request->input('status'),
            'page' => (int) $request->input('page', 1),
            'per_page' => (int) $request->input('per_page', 10),
            'sort' => $request->input('sort', 'created_at'),
            'order' => $request->input('order', 'desc'),
        ];
    }
}
