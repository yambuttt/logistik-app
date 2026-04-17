<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverVehicleAssignment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayAssignment = DriverVehicleAssignment::with('vehicle')
            ->where('driver_user_id', auth()->id())
            ->where('assignment_date', now()->toDateString())
            ->first();

        return view('driver.dashboard', compact('todayAssignment'));
    }
}