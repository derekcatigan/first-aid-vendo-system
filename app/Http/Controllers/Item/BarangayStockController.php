<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Models\BarangayStock;
use App\Models\Item;
use App\Models\ItemLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BarangayStockController extends Controller
{
    /**
     * Store a new barangay stock item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemName' => [
                'required',
                'string',
                'max:255',
                // Prevent duplicate names in BarangayStock
                Rule::unique('barangay_stocks', 'item_name')
            ],
            'itemDescription' => 'nullable|string',
            'itemQuantity' => 'required|integer|min:0',
            'itemLowStock' => 'nullable|integer|min:0',
        ]);

        try {
            $bItem = BarangayStock::create([
                'item_name' => Str::title($validated['itemName']),
                'description' => $validated['itemDescription'],
                'quantity' => $validated['itemQuantity'],
                'low_stock_threshold' => $validated['itemLowStock'] ?? 0,
            ]);

            return response()->json([
                'message' => 'Barangay stock added successfully',
                'barangayItem' => $bItem
            ]);
        } catch (Exception $e) {
            Log::error('Barangay store failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add barangay stock'], 500);
        }
    }

    /**
     * Transfer barangay stock to vending machine
     */
    public function transfer(Request $request, BarangayStock $barangayStock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'keypad' => [
                'required',
                'integer',
                'min:1',
                'max:8',
                Rule::unique('items', 'keypad')->where(fn($q) => $q->where('is_active', true))
            ],
            'motor_index' => [
                'required',
                'integer',
                'min:1',
                'max:8',
                Rule::unique('items', 'motor_index')->where(fn($q) => $q->where('is_active', true))
            ],
        ]);

        if ($validated['quantity'] > $barangayStock->quantity) {
            return response()->json(['message' => 'Transfer quantity exceeds barangay stock'], 422);
        }

        // Prevent creating duplicate Vendo item (same name)
        if (Item::where('item_name', $barangayStock->item_name)->exists()) {
            return response()->json(['message' => 'Vending machine already has this item'], 422);
        }

        DB::beginTransaction();
        try {
            // Deduct from barangay stock
            $barangayStock->decrement('quantity', $validated['quantity']);

            // Create vending item
            $item = Item::create([
                'item_name' => $barangayStock->item_name,
                'description' => $barangayStock->description,
                'quantity' => $validated['quantity'],
                'keypad' => $validated['keypad'],
                'motor_index' => $validated['motor_index'],
                'low_stock_threshold' => $barangayStock->low_stock_threshold,
                'is_active' => true,
            ]);

            // Log initial add
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => $validated['quantity'],
                'log_type' => 'Transferred from Barangay Stock',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transferred to vending machine successfully',
                'remainingBarangayQty' => $barangayStock->quantity,
                'vendoItem' => $item
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Barangay transfer failed: ' . $e->getMessage());
            return response()->json(['message' => 'Transfer failed'], 500);
        }
    }
}
