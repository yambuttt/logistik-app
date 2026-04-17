<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Stock;

class InventoryService
{
    public function stockIn(array $data): void
    {
        $stock = Stock::firstOrCreate(
            [
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $data['product_id'],
            ],
            [
                'qty' => 0,
            ]
        );

        $before = $stock->qty;
        $after = $before + $data['qty'];

        $stock->update([
            'qty' => $after,
        ]);

        InventoryMovement::create([
            'transaction_date' => $data['transaction_date'],
            'warehouse_id' => $data['warehouse_id'],
            'product_id' => $data['product_id'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'movement_type' => 'goods_in',
            'qty_in' => $data['qty'],
            'qty_out' => 0,
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }
}