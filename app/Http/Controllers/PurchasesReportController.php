<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase; // Assuming the model name is Purchase
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PurchasesReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Filter purchases by date range using 'created_at'
    public function filterByDateRange(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = $validatedData['start_date'];
            $endDate = $validatedData['end_date'];

            // Retrieve all purchases within the date range using 'created_at'
            $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->with(['itemcategory']) // Assuming relationships exist in the Purchase model
                ->orderBy('created_at', 'desc')
                ->get();

            // If no purchases are found, return a 404 error
            if ($purchases->isEmpty()) {
                return response()->json(['message' => 'No purchases found in the specified date range'], Response::HTTP_NOT_FOUND);
            }

            // Return the purchases data with a 200 status code
            return response()->json($purchases, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to filter purchases by date range: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to filter purchases by date range'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
