<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTrip;
use App\Models\DriverVehicleAssignment;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NearestNeighborRouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryTripController extends Controller
{
    public function index(): View
    {
        $warehouseId = auth()->user()->warehouse_id;

        $trips = DeliveryTrip::with(['driver', 'vehicle', 'shipments.order'])
            ->where('warehouse_id', $warehouseId)
            ->latest()
            ->paginate(10);

        return view('warehouse.delivery-trips.index', compact('trips'));
    }

    public function create(): View
    {
        $warehouseId = auth()->user()->warehouse_id;
        $tripDate = now()->toDateString();

        $shipments = Shipment::with('order')
            ->where('warehouse_id', $warehouseId)
            ->whereDate('shipment_date', $tripDate)
            ->whereIn('status', ['assigned', 'waiting_driver'])
            ->whereDoesntHave('tripShipments')
            ->get();

        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('warehouse.delivery-trips.create', compact('shipments', 'drivers', 'vehicles', 'tripDate'));
    }

    public function store(Request $request, NearestNeighborRouteService $nearestNeighborRouteService): RedirectResponse
    {
        $warehouseId = auth()->user()->warehouse_id;

        $validated = $request->validate([
            'trip_date' => ['required', 'date'],
            'driver_user_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['required', 'exists:shipments,id'],
            'notes' => ['nullable', 'string'],
        ], [
            'trip_date.required' => 'Tanggal trip wajib diisi.',
            'driver_user_id.required' => 'Driver wajib dipilih.',
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'shipment_ids.required' => 'Pilih minimal satu shipment.',
        ]);

        $driverAssignment = DriverVehicleAssignment::where('driver_user_id', $validated['driver_user_id'])
            ->where('assignment_date', $validated['trip_date'])
            ->first();

        if (! $driverAssignment) {
            return back()
                ->withErrors(['driver_user_id' => 'Driver belum punya assignment kendaraan pada tanggal tersebut.'])
                ->withInput();
        }

        if ((int) $driverAssignment->vehicle_id !== (int) $validated['vehicle_id']) {
            return back()
                ->withErrors(['vehicle_id' => 'Kendaraan harus sesuai dengan assignment driver pada tanggal trip.'])
                ->withInput();
        }

        $shipments = Shipment::with('order')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('id', $validated['shipment_ids'])
            ->whereDoesntHave('tripShipments')
            ->get();

        if ($shipments->count() !== count($validated['shipment_ids'])) {
            return back()
                ->withErrors(['shipment_ids' => 'Ada shipment yang sudah masuk ke trip lain.'])
                ->withInput();
        }

        $warehouse = auth()->user()->warehouse;

        if (! $warehouse || ! $warehouse->latitude || ! $warehouse->longitude) {
            return back()
                ->withErrors(['shipment_ids' => 'Lokasi gudang belum diatur.'])
                ->withInput();
        }

        foreach ($shipments as $shipment) {
            if (! optional($shipment->order)->delivery_latitude || ! optional($shipment->order)->delivery_longitude) {
                return back()
                    ->withErrors(['shipment_ids' => 'Ada shipment yang belum punya koordinat customer.'])
                    ->withInput();
            }
        }

        $sortedRoutes = $nearestNeighborRouteService->generate(
            $shipments,
            (float) $warehouse->latitude,
            (float) $warehouse->longitude
        );

        DB::transaction(function () use ($validated, $warehouseId, $sortedRoutes) {
            $trip = DeliveryTrip::create([
                'trip_number' => 'TRIP-' . now()->format('YmdHis'),
                'trip_date' => $validated['trip_date'],
                'warehouse_id' => $warehouseId,
                'driver_user_id' => $validated['driver_user_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'status' => 'planned',
                'total_shipments' => count($sortedRoutes),
                'total_estimated_distance_km' => collect($sortedRoutes)->sum('distance_from_previous_km'),
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($sortedRoutes as $route) {
                $trip->tripShipments()->create([
                    'shipment_id' => $route['shipment']->id,
                    'route_order' => $route['route_order'],
                    'distance_from_previous_km' => $route['distance_from_previous_km'],
                ]);

                $route['shipment']->update([
                    'driver_user_id' => $validated['driver_user_id'],
                    'vehicle_id' => $validated['vehicle_id'],
                    'status' => 'assigned',
                ]);
            }

            User::where('id', $validated['driver_user_id'])->update([
                'availability_status' => 'assigned',
            ]);
        });

        return redirect()
            ->route('warehouse.delivery-trips.index')
            ->with('success', 'Trip berhasil dibuat dan rute otomatis diurutkan dengan Nearest Neighbor.');
    }
}