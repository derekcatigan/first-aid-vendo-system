<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();

        $lowStockItems = Item::whereColumn('quantity', '<=', 'low_stock_threshold')->count();

        $restockedToday = Item::whereDate('updated_at', now()->toDateString())->count();

        $recentLogs = ItemLog::with(['item', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $items = Item::orderBy('item_name')->get();

        return view('dashboard', [
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'restockedToday' => $restockedToday,
            'recentLogs' => $recentLogs,
            'items' => $items,
        ]);
    }

    public function restock(Request $request, Item $item)
    {
        // Validate input
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $amount = (int) $request->amount;

            // Update item quantity
            $item->quantity += $amount;
            $item->save();

            // Log the restock
            ItemLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'log_type' => 'restock',
                'quantity_change' => $amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'newQuantity' => $item->quantity,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to restock item ID ' . $item->id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Restock failed, please try again.'
            ], 500);
        }
    }

    public function summary()
    {
        $totalItems = Item::count();
        $lowStockItems = Item::whereColumn('quantity', '<=', 'low_stock_threshold')->count();
        $restockedToday = Item::whereDate('updated_at', now()->toDateString())->count();

        return response()->json([
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'restockedToday' => $restockedToday,
        ]);
    }

    public function recentLogs()
    {
        $logs = ItemLog::with(['item', 'user'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'log_type' => $log->log_type,
                    'item' => $log->item ? ['item_name' => $log->item->item_name] : null,
                    'quantity_change' => $log->quantity_change,
                    'user' => $log->user ? ['name' => $log->user->name] : null,
                    'created_at_formatted' => $log->created_at->format('M d, Y h:i A')
                        . ' — '
                        . $log->created_at->diffForHumans(),
                ];
            });

        return response()->json($logs);
    }


    public function analytics()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Restock counts per day
        $restockData = ItemLog::selectRaw('DATE(created_at) as date, SUM(quantity_change) as total')
            ->where('log_type', 'restock')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Dispense counts per day
        $dispenseData = ItemLog::selectRaw('DATE(created_at) as date, SUM(ABS(quantity_change)) as total')
            ->where('log_type', 'dispense')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare arrays for labels (dates) and data
        $days = [];
        $restockCounts = [];
        $dispenseCounts = [];

        $period = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);
        foreach ($period as $day) {
            $days[] = $day->format('Y-m-d');
            $restockCounts[] = $restockData->firstWhere('date', $day->format('Y-m-d'))->total ?? 0;
            $dispenseCounts[] = $dispenseData->firstWhere('date', $day->format('Y-m-d'))->total ?? 0;
        }

        return response()->json([
            'labels' => $days,
            'restock' => $restockCounts,
            'dispense' => $dispenseCounts,
        ]);
    }
}
