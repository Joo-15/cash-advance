<?php

namespace App\Observers;

use App\Models\CashAdvance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CashAdvanceObserver
{
    /**
     * Handle events BEFORE new data is created.
     * (TAMBAHKAN MANUAL)
     */
    public function creating(CashAdvance $cashAdvance): void
    {
        if (empty($cashAdvance->user_id)) {
            $cashAdvance->user_id = Auth::id();
        }
    }
    /**
     * Handle the CashAdvance "created" event.
     */
    public function created(CashAdvance $cashAdvance): void {}

    /**
     * Handle the CashAdvance "updated" event.
     */
    public function updated(CashAdvance $cashAdvance): void
    {
        //
    }

    /**
     * Handle the CashAdvance "deleted" event.
     */
    public function deleted(CashAdvance $cashAdvance): void
    {
        //
    }

    /**
     * Handle the CashAdvance "restored" event.
     */
    public function restored(CashAdvance $cashAdvance): void
    {
        //
    }

    /**
     * Handle the CashAdvance "force deleted" event.
     */
    public function forceDeleted(CashAdvance $cashAdvance): void
    {
        //
    }
}
