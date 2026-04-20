@extends('layouts.warehouse')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Warehouse Delivery</p>
        <h2 class="text-2xl font-bold text-slate-900">Buat Delivery Trip</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih beberapa shipment, lalu sistem akan mengurutkan rute otomatis.</p>
    </div>

    <div class="glass-panel rounded-[24px] border border-white/50 p-6 shadow-lg">
        <form action="{{ route('warehouse.delivery-trips.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Trip</label>
                    <input type="date" name="trip_date" value="{{ old('trip_date', $tripDate) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none">
                    @error('trip_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Driver</label>
                    <select name="driver_user_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none">
                        <option value="">Pilih driver</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(old('driver_user_id') == $driver->id)>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_user_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Kendaraan</label>
                    <select name="vehicle_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none">
                        <option value="">Pilih kendaraan</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                {{ $vehicle->name }} - {{ $vehicle->plate_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-3 block text-sm font-semibold text-slate-700">Pilih Shipment</label>
                @error('shipment_ids')
                    <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="space-y-3">
                    @forelse ($shipments as $shipment)
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                            <input type="checkbox" name="shipment_ids[]" value="{{ $shipment->id }}"
                                class="mt-1 h-4 w-4 rounded border-slate-300"
                                @checked(is_array(old('shipment_ids')) && in_array($shipment->id, old('shipment_ids')))>

                            <div>
                                <div class="font-semibold text-slate-900">
                                    {{ $shipment->shipment_number }} - {{ $shipment->order->customer_name ?? '-' }}
                                </div>
                                <div class="text-sm text-slate-600">
                                    {{ $shipment->order->delivery_address ?? '-' }}
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 text-sm text-slate-500">
                            Belum ada shipment yang siap dimasukkan ke trip.
                        </div>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan</label>
                <textarea name="notes" rows="3"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none">{{ old('notes') }}</textarea>
            </div>

            <button type="submit"
                class="rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white">
                Simpan Trip + Urutkan Rute
            </button>
        </form>
    </div>
</div>
@endsection