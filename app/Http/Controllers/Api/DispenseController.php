<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DispenseController extends Controller
{
    public function dispense(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming data
        $validator = Validator::make($data, [
            'motor_index' => 'required|integer|min:0|max:7',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Arduino sends motor_index 0-7; convert to keypad string "1"-"8"
        $motorIndex = $data['motor_index'];
        $quantity = $data['quantity'];
        $keypad = (string) ($motorIndex + 1);

        // Find item by keypad value
        $item = Item::where('keypad', $keypad)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found for this keypad'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Deduct quantity
            $item->quantity -= $quantity;
            if ($item->quantity < 0) {
                $item->quantity = 0;
            }
            $item->save();

            // Log the dispense
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => null,
                'log_type' => 'dispense',
                'quantity_change' => -$quantity,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'item' => $item->item_name,
                'newQuantity' => $item->quantity,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to dispense item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispense item'
            ], 500);
        }
    }
}
