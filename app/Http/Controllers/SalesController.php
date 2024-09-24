<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\Sale;
use Symfony\Component\HttpFoundation\Response;

class SalesController extends Controller
{
      // Get all sales
    public function index()
    {
        try {
            // Eager load the item relationship
            $sales = Sale::with('item')->get();

            // Transform the result to include item_name instead of item_id
            $sales = $sales->map(function ($sale) {
                return [
                    'sale_id' => $sale->sale_id,
                    'item_name' => $sale->item->item_name,
                    'amount_distributed' => $sale->amount_distributed,
                    'customername' => $sale->customername,
                    'phone_number' => $sale->phone_number,
                    'payment_method' => $sale->payment_method,
                    'sold_at' => $sale->sold_at,
                    'total_amount' => $sale->total_amount,
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at,
                ];
            });

            return response()->json($sales, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve sales', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to retrieve sales'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Show a specific sale by ID
    public function show($sale_id)
    {
        try {
            // Find the sale by its ID
            $sale = Sale::with('item')->findOrFail($sale_id);

            // Transform the sale data
            $result = [
                'sale_id' => $sale->sale_id,
                'item_name' => $sale->item->item_name,
                'amount_distributed' => $sale->amount_distributed,
                'customername' => $sale->customername,
                'phone_number' => $sale->phone_number,
                'payment_method' => $sale->payment_method,
                'sold_at' => $sale->sold_at,
                'total_amount' => $sale->total_amount,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ];

            return response()->json($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve sale', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Sale not found'], Response::HTTP_NOT_FOUND);
        }
    }

    // Create a new sale
    public function store(Request $request)
    {
        // Validate the required attributes
        $validatedData = $request->validate([
            'item_id' => 'required|integer|exists:items,item_id',
            'amount_distributed' => 'required|integer|min:0',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'customername' => 'required|string',
            'phone_number' => 'required|string',
            'sold_at' => 'required|date_format:Y-m-d',
        ]);

        // Start a transaction
        DB::beginTransaction();

        try {
            // Find the item by its ID
            $item = Item::findOrFail($validatedData['item_id']);
            $currentStock = $item->amount_distributed;

            // Calculate the total amount distributed for the item
            $totalDistributed = Sale::where('item_id', $validatedData['item_id'])->sum('amount_distributed');
            $newTotalDistributed = $totalDistributed + $validatedData['amount_distributed'];

            // Log the sale processing
            Log::info('Processing sale', [
                'item_id' => $validatedData['item_id'],
                'current_stock' => $currentStock,
                'total_distributed' => $totalDistributed,
                'new_total_distributed' => $newTotalDistributed,
                'requested_amount' => $validatedData['amount_distributed']
            ]);

            // Check for sufficient stock
            if ($validatedData['amount_distributed'] <= $currentStock) {
                // Update stock and create sale
                $item->update(['amount_distributed' => $currentStock - $validatedData['amount_distributed']]);
                $sale = Sale::create($validatedData + ['item_id' => $validatedData['item_id']]);

                // Commit transaction
                DB::commit();

                return response()->json([
                    'message' => 'Item updated and sale recorded successfully',
                    'item' => $item,
                    'sale' => $sale,
                ], Response::HTTP_OK);
            } else {
                // Rollback on insufficient stock
                DB::rollBack();
                return response()->json(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            // Rollback on errors
            DB::rollBack();
            Log::error('Failed to process sale', ['error' => $e->getMessage(), 'request_data' => $request->all()]);
            return response()->json(['error' => 'Failed to process sale'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update an existing sale
    public function update(Request $request, $sale_id)
    {
        $validatedData = $request->validate([
            'amount_distributed' => 'required|integer|min:0',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'customername' => 'required|string',
            'phone_number' => 'required|string',
            'sold_at' => 'required|date_format:Y-m-d',
        ]);

        try {
            // Find and update the sale
            $sale = Sale::findOrFail($sale_id);
            $sale->update($validatedData);

            return response()->json([
                'message' => 'Sale updated successfully',
                'sale' => $sale,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to update sale', ['error' => $e->getMessage(), 'request_data' => $request->all()]);
            return response()->json(['error' => 'Failed to update sale'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete a sale
    public function destroy($sale_id)
    {
        try {
            $sale = Sale::findOrFail($sale_id);
            $sale->delete();

            return response()->json(['message' => 'Sale deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to delete sale', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to delete sale'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



// Get all sales
    public function cashSales()
    {
        try {
            // Eager load the item relationship
            $sales = Sale::with('item')->where('payment_method', 'Cash')->get();

            // Transform the result to include item_name instead of item_id
            $sales = $sales->map(function ($sale) {
                return [
                    'sale_id' => $sale->sale_id,
                    'item_name' => $sale->item->item_name,
                    'amount_distributed' => $sale->amount_distributed,
                    'customername' => $sale->customername,
                    'phone_number' => $sale->phone_number,
                    'payment_method' => $sale->payment_method,
                    'sold_at' => $sale->sold_at,
                    'total_amount' => $sale->total_amount,
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at,
                ];
            });

            return response()->json($sales, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve sales', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to retrieve sales'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


     // Get all sales
    public function creditSales()
    {
        try {
            // Eager load the item relationship
            $sales = Sale::with('item')->where('payment_method', 'Credit')->get();

            // Transform the result to include item_name instead of item_id
            $sales = $sales->map(function ($sale) {
                return [
                    'sale_id' => $sale->sale_id,
                    'item_name' => $sale->item->item_name,
                    'amount_distributed' => $sale->amount_distributed,
                    'customername' => $sale->customername,
                    'phone_number' => $sale->phone_number,
                    'payment_method' => $sale->payment_method,
                    'sold_at' => $sale->sold_at,
                    'total_amount' => $sale->total_amount,
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at,
                ];
            });

            return response()->json($sales, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve sales', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to retrieve sales'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Filter sales report by date range (created_at)
public function salesReportByDate(Request $request)
{
    // Validate the date range input
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date' => 'required|date_format:Y-m-d',
    ]);

    try {
        // Parse start and end dates
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Filter sales by created_at between start_date and end_date
        $sales = Sale::with('item')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Transform the result to include item_name instead of item_id
        $sales = $sales->map(function ($sale) {
            return [
                'sale_id' => $sale->sale_id,
                'item_name' => $sale->item->item_name,
                'amount_distributed' => $sale->amount_distributed,
                'customername' => $sale->customername,
                'phone_number' => $sale->phone_number,
                'payment_method' => $sale->payment_method,
                'sold_at' => $sale->sold_at,
                'total_amount' => $sale->total_amount,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ];
        });

        return response()->json($sales, Response::HTTP_OK);
    } catch (\Exception $e) {
        Log::error('Failed to retrieve sales by date range', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to retrieve sales'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


}
