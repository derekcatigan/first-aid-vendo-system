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

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::query()->orderBy('item_name', 'asc')->get();
        $barangayStocks = BarangayStock::query()->orderBy('item_name', 'asc')->get();

        return view('item-inventory', compact('items', 'barangayStocks'));
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

    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);

            // --- NEW SAFETY CHECK ---
            // Prevent deleting if there is still stock in the machine
            if ($item->quantity > 0) {
                return response()->json([
                    'message' => "Cannot delete. There are still {$item->quantity} units in the machine. Please empty the machine first."
                ], 422); // 422 is standard for validation/logic errors
            }

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

    public function deduct(Item $item)
    {
        if ($item->quantity < 1) {
            return response()->json([
                'message' => 'No stock left to deduct.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $item->decrement('quantity', 1);

            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'quantity_change' => -1,
                'log_type' => 'Vendo Deduction',
            ]);

            DB::commit();

            return response()->json([
                'message' => '1 unit deducted'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Deduction failed'], 500);
        }
    }
}
