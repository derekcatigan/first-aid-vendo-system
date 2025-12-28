<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('item_name')->get();

        return view('item-inventory', compact('items'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemName'        => 'required|string|max:255',
            'itemDescription' => 'nullable|string',
            'itemQuantity'    => 'required|integer|min:0',
            'itemKey' => [
                'required',
                'integer',
                Rule::unique('items', 'keypad')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],

            'itemMotor' => [
                'required',
                'integer',
                Rule::unique('items', 'motor_index')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'itemLowStock'    => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {

            $item = Item::create([
                'item_name'           => Str::title($validated['itemName']),
                'description'         => $validated['itemDescription'],
                'quantity'            => $validated['itemQuantity'],
                'keypad'              => $validated['itemKey'],
                'motor_index'         => $validated['itemMotor'],
                'low_stock_threshold' => $validated['itemLowStock'],
            ]);

            ItemLog::create([
                'item_id'         => $item->id,
                'user_id'         => Auth::id(),
                'quantity_change' => $validated['itemQuantity'],
                'log_type'        => 'Initial Add',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Item created successfully',
                'item' => $item
            ]);
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Item store failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create item.',
            ], 500);
        }
    }

    public function toggleStatus(Item $item)
    {
        DB::beginTransaction();

        try {
            // If enabling, check hardware conflicts
            if (! $item->is_active) {

                // Max 8 active items rule
                $activeCount = Item::where('is_active', true)->count();
                if ($activeCount >= 8) {
                    return response()->json([
                        'message' => 'Only 8 active items are allowed in the dispenser.'
                    ], 422);
                }

                // Check keypad / motor conflict
                $conflict = Item::where('is_active', true)
                    ->where(function ($q) use ($item) {
                        $q->where('keypad', $item->keypad)
                            ->orWhere('motor_index', $item->motor_index);
                    })
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'message' => 'Cannot enable item. Keypad or motor is already in use.'
                    ], 422);
                }
            }

            $item->update([
                'is_active' => ! $item->is_active,
            ]);

            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => 0,
                'log_type' => $item->is_active ? 'enabled' : 'disabled',
            ]);

            DB::commit();

            return response()->json([
                'message' => $item->is_active ? 'Item enabled' : 'Item disabled',
                'is_active' => $item->is_active,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Failed to update item status'
            ], 500);
        }
    }

    public function updateQuantity(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $oldQty = $item->quantity;
            $newQty = $validated['quantity'];
            $change = $newQty - $oldQty;

            $item->update([
                'quantity' => $newQty,
            ]);

            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => $change,
                'log_type' => 'update',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Quantity updated successfully',
                'quantity' => $newQty
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Failed to update quantity'
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);

            DB::beginTransaction();

            // Delete associated logs
            ItemLog::where('item_id', $id)->delete();

            // Delete item
            $item->delete();

            DB::commit();

            return response()->json([
                'message' => 'Item deleted successfully'
            ]);
        } catch (Exception $e) {

            DB::rollBack();
            Log::error('Item delete failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete item.'
            ], 500);
        }
    }
}
