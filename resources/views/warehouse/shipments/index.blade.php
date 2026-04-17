@extends('layouts.warehouse')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">
                Warehouse Delivery
            </p>
            <h2 class="text-2xl font-bold text-slate-900">
                Pengiriman
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Kelola pengiriman untuk gudang Anda.
            </p>
        </div>

        <a
            href="{{ route('warehouse.shipments.create') }}"
            class="inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-800"
        >
            + Buat Pengiriman
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel rounded-[24px] border border-white/50 p-6 shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-y-3">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">No Pengiriman</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Tanggal</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Pesanan</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Driver</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Kendaraan</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shipments as $shipment)
                        <tr class="rounded-2xl bg-white shadow-sm align-top">
                            <td class="rounded-l-2xl px-4 py-4 font-semibold text-slate-900">
                                {{ $shipment->shipment_number }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600">
                                {{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $shipment->order->order_number ?? '-' }}</div>
                                <div>{{ $shipment->order->customer_name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ $shipment->driver->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                @if ($shipment->vehicle)
                                    <div class="font-semibold text-slate-900">{{ $shipment->vehicle->name }}</div>
                                    <div>{{ $shipment->vehicle->plate_number }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="rounded-r-2xl px-4 py-4 text-sm">
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                    {{ ucfirst($shipment->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                Belum ada pengiriman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $shipments->links() }}
        </div>
    </div>
</div>
@endsection