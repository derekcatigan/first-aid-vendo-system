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

        // 2. NEW CHECK: Prevent the same item name from being in two different slots
        // We look for any item with the same name that IS NOT in the requested slot.
        $duplicateItem = Item::where('item_name', $barangayStock->item_name)
            ->where(function ($q) use ($validated) {
                $q->where('keypad', '!=', $validated['keypad'])
                    ->orWhere('motor_index', '!=', $validated['motor_index']);
            })->first();

        if ($duplicateItem) {
            return response()->json([
                'message' => " '{$barangayStock->item_name}' is already assigned to Keypad {$duplicateItem->keypad} / Motor {$duplicateItem->motor_index}. Please restock the existing slot instead."
            ], 422);
        }

        // 3. HARDWARE CONFLICT CHECK: Is this slot taken by a DIFFERENT active item?
        $slotConflict = Item::where('is_active', true)
            ->where(function ($query) use ($validated) {
                $query->where('keypad', $validated['keypad'])
                    ->orWhere('motor_index', $validated['motor_index']);
            })
            ->where('item_name', '!=', $barangayStock->item_name) // Conflict only if it's a different name
            ->first();

        if ($slotConflict) {
            return response()->json([
                'message' => "Slot (K{$validated['keypad']}/M{$validated['motor_index']}) is currently used by '{$slotConflict->item_name}'."
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Deduct from barangay
            $barangayStock->decrement('quantity', $validated['quantity']);

            // 4. Update existing or Create new
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

            $item->increment('quantity', $validated['quantity']);

            // 5. Log it
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => $validated['quantity'],
                'log_type' => 'Transferred from Barangay Stock',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transfer successful',
                'remainingBarangayQty' => $barangayStock->quantity,
                'vendoItem' => $item
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
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

    public function deduct(BarangayStock $barangayStock)
    {
        if ($barangayStock->quantity < 1) {
            return response()->json([
                'message' => 'No stock left to deduct.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $barangayStock->decrement('quantity', 1);

            ItemLog::create([
                'item_id' => null,
                'user_id' => Auth::id(),
                'quantity_change' => -1,
                'log_type' => "Barangay Deduction: {$barangayStock->item_name}",
            ]);

            DB::commit();

            return response()->json([
                'message' => '1 unit deducted',
                'new_quantity' => $barangayStock->quantity
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Deduction failed'], 500);
        }
    }
}
