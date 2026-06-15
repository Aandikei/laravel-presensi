<?php

namespace App\Observers;

use App\Models\OrangTua;

class OrangTuaObserver
{
    public function deleted(OrangTua $ortu): void
    {
        $ortu->user?->delete();
    }
}