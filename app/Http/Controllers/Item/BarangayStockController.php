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
                Rule::unique('barangay_stocks', 'item_name')
            ],
            'itemDescription' => 'nullable|string',
            'itemQuantity' => 'required|integer|min:0',
            'itemLowStock' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction(); // Recommended when doing multiple inserts
        try {
            // 1. Create the Barangay Stock
            $bItem = BarangayStock::create([
                'item_name' => Str::title($validated['itemName']),
                'description' => $validated['itemDescription'],
                'quantity' => $validated['itemQuantity'],
                'low_stock_threshold' => $validated['itemLowStock'] ?? 0,
            ]);

            // 2. Create the Log
            ItemLog::create([
                'item_id' => null, // Since it's not in the Vendo yet
                'user_id' => Auth::id(),
                'quantity_change' => $validated['itemQuantity'],
                'log_type' => "Initial Barangay Stock Entry: " . $bItem->item_name,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Barangay stock added and logged successfully',
                'barangayItem' => $bItem
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Barangay store/log failed: ' . $e->getMessage());
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
            'keypad' => 'required|integer|min:1|max:8',
            'motor_index' => 'required|integer|min:1|max:8',
        ]);

        // 1. Check if Barangay has enough stock
        if ($validated['quantity'] > $barangayStock->quantity) {
            return response()->json(['message' => 'Transfer quantity exceeds barangay stock'], 422);
        }

        // 2. Find if any item is ALREADY using this keypad or motor
        $existingItem = Item::where('is_active', true)
            ->where(function ($query) use ($validated) {
                $query->where('keypad', $validated['keypad'])
                    ->orWhere('motor_index', $validated['motor_index']);
            })->first();

        if ($existingItem) {
            // ERROR: Different item is occupying the slot
            if ($existingItem->item_name !== $barangayStock->item_name) {
                return response()->json([
                    'message' => "Slot (Keypad {$validated['keypad']}/Motor {$validated['motor_index']}) is already occupied by '{$existingItem->item_name}'."
                ], 422);
            }
            // SUCCESS PATH A: Same item exists, we will increment it
        }

        DB::beginTransaction();
        try {
            // Deduct from barangay stock
            $barangayStock->decrement('quantity', $validated['quantity']);

            // 3. Update existing or Create new
            $item = Item::updateOrCreate(
                [
                    'item_name'   => $barangayStock->item_name,
                    'keypad'      => $validated['keypad'],
                    'motor_index' => $validated['motor_index']
                ],
                [
                    'description'         => $barangayStock->description,
                    'low_stock_threshold' => $barangayStock->low_stock_threshold,
                    'is_active'           => true,
                ]
            );

            // Increment the quantity (works for both new and existing)
            $item->increment('quantity', $validated['quantity']);

            // 4. Log the movement
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => $validated['quantity'],
                'log_type' => 'Transferred from Barangay Stock',
            ]);

            DB::commit();

            return response()->json([
                'message' => $existingItem ? 'Quantity added to existing Vendo item' : 'New item added to Vendo',
                'remainingBarangayQty' => $barangayStock->quantity,
                'vendoItem' => $item
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Barangay transfer failed: ' . $e->getMessage());
            return response()->json(['message' => 'Transfer failed'], 500);
        }
    }

    /**
     * Add more quantity to an existing barangay stock item
     */
    public function restock(Request $request, BarangayStock $barangayStock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $barangayStock->increment('quantity', $validated['quantity']);

            ItemLog::create([
                'item_id' => null,
                'user_id' => Auth::id(),
                'quantity_change' => $validated['quantity'],
                'log_type' => "Barangay Restock: " . $barangayStock->item_name,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Restocked and logged successfully',
                'new_quantity' => $barangayStock->quantity
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Restock failed'], 500);
        }
    }

    /**
     * Delete a barangay stock item
     */
    public function destroy(BarangayStock $barangayStock)
    {
        // 1. Prevent deleting if quantity remains
        if ($barangayStock->quantity > 0) {
            return response()->json([
                // Using a structured message for the SweetAlert
                'message' => "Stock cannot be deleted. There are still {$barangayStock->quantity} units remaining. Please transfer or clear the stock first."
            ], 422); // 422 Unprocessable Entity
        }

        try {
            $barangayStock->delete();

            return response()->json([
                'message' => 'Barangay stock item removed successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Barangay delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'An internal error occurred.'], 500);
        }
    }
}
