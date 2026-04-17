<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\DriverVehicleAssignment;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\ShipmentCapacityService;

class ShipmentController extends Controller
{
    public function index(): View
    {
        $warehouseId = auth()->user()->warehouse_id;

        $shipments = Shipment::with(['order', 'driver', 'vehicle', 'items.product'])
            ->where('warehouse_id', $warehouseId)
            ->latest()
            ->paginate(10);

        return view('warehouse.shipments.index', compact('shipments'));
    }

    public function create(): View
    {
        $warehouseId = auth()->user()->warehouse_id;

        $orders = Order::with('items.product')
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'draft')
            ->latest()
            ->get();

        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('warehouse.shipments.create', compact('orders', 'drivers'));
    }

    public function store(Request $request, ShipmentCapacityService $shipmentCapacityService): RedirectResponse
    {
        $warehouseId = auth()->user()->warehouse_id;

        $validated = $request->validate([
            'shipment_date' => ['required', 'date'],
            'order_id' => ['required', 'exists:orders,id'],
            'driver_user_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ], [
            'shipment_date.required' => 'Tanggal pengiriman wajib diisi.',
            'order_id.required' => 'Pesanan wajib dipilih.',
            'driver_user_id.required' => 'Driver wajib dipilih.',
        ]);

        $order = Order::with(['items.product'])
            ->where('warehouse_id', $warehouseId)
            ->findOrFail($validated['order_id']);

        $driverAssignment = DriverVehicleAssignment::with('vehicle')
            ->where('driver_user_id', $validated['driver_user_id'])
            ->where('assignment_date', $validated['shipment_date'])
            ->first();

        if (!$driverAssignment || !$driverAssignment->vehicle) {
            return back()
                ->withErrors([
                    'driver_user_id' => 'Driver belum memiliki kendaraan aktif pada tanggal pengiriman tersebut.',
                ])
                ->withInput();
        }

        $capacityErrors = $shipmentCapacityService->validateOrderAgainstVehicle(
            $order,
            $driverAssignment->vehicle_id
        );

        if (!empty($capacityErrors)) {
            return back()
                ->withErrors([
                    'order_id' => implode(' ', $capacityErrors),
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $warehouseId, $order, $driverAssignment) {
            $shipment = Shipment::create([
                'shipment_number' => 'SHP-' . now()->format('YmdHis'),
                'shipment_date' => $validated['shipment_date'],
                'warehouse_id' => $warehouseId,
                'order_id' => $order->id,
                'driver_user_id' => $validated['driver_user_id'],
                'vehicle_id' => $driverAssignment->vehicle_id,
                'status' => 'assigned',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($order->items as $item) {
                $shipment->items()->create([
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                ]);
            }

            $order->update([
                'status' => 'ready',
            ]);
        });

        return redirect()
            ->route('warehouse.shipments.index')
            ->with('success', 'Pengiriman berhasil dibuat.');
    }
}