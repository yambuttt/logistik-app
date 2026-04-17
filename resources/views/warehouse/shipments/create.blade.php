@extends('layouts.warehouse')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">
            Warehouse Delivery
        </p>
        <h2 class="text-2xl font-bold text-slate-900">
            Buat Pengiriman
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            Buat pengiriman dari pesanan yang sudah tersedia.
        </p>
    </div>

    <div class="glass-panel rounded-[24px] border border-white/50 p-6 shadow-lg">
        <form action="{{ route('warehouse.shipments.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Pengiriman</label>
                    <input
                        type="date"
                        name="shipment_date"
                        value="{{ old('shipment_date', now()->format('Y-m-d')) }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-900 focus:ring-4 focus:ring-emerald-100"
                    >
                    @error('shipment_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Pesanan</label>
                    <select
                        name="order_id"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-900 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="">Pilih pesanan</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                                {{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Driver</label>
                    <select
                        name="driver_user_id"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-900 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="">Pilih driver</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(old('driver_user_id') == $driver->id)>
                                {{ $driver->name }} - {{ $driver->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_user_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan</label>
                    <textarea
                        name="notes"
                        rows="4"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-900 focus:ring-4 focus:ring-emerald-100"
                        placeholder="Catatan tambahan"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-800"
                >
                    Simpan Pengiriman
                </button>

                <a
                    href="{{ route('warehouse.shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection