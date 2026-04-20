<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTrip;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryTripController extends Controller
{
    public function index(): View
    {
        $trips = DeliveryTrip::with([
                'vehicle',
                'tripShipments.shipment.order',
                'tripShipments.shipment.items.product',
            ])
            ->where('driver_user_id', auth()->id())
            ->whereIn('status', ['planned', 'on_trip'])
            ->latest()
            ->paginate(10);

        return view('driver.delivery-trips.index', compact('trips'));
    }

    public function start(DeliveryTrip $deliveryTrip): RedirectResponse
    {
        abort_unless($deliveryTrip->driver_user_id === auth()->id(), 403);

        $deliveryTrip->update([
            'status' => 'on_trip',
        ]);

        auth()->user()->update([
            'availability_status' => 'on_delivery',
        ]);

        return back()->with('success', 'Trip dimulai.');
    }

    public function completeStop(DeliveryTrip $deliveryTrip, int $tripShipmentId): RedirectResponse
    {
        abort_unless($deliveryTrip->driver_user_id === auth()->id(), 403);

        $tripShipment = $deliveryTrip->tripShipments()
            ->with('shipment')
            ->findOrFail($tripShipmentId);

        $tripShipment->shipment->update([
            'status' => 'completed',
        ]);

        return back()->with('success', 'Stop berhasil diselesaikan.');
    }

    public function finish(DeliveryTrip $deliveryTrip): RedirectResponse
    {
        abort_unless($deliveryTrip->driver_user_id === auth()->id(), 403);

        $deliveryTrip->update([
            'status' => 'completed',
        ]);

        auth()->user()->update([
            'availability_status' => 'available',
        ]);

        return back()->with('success', 'Trip selesai.');
    }
}