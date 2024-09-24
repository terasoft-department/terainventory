<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }


    // Get all purchases
    public function index(Request $request)
    {
        $filters = $request->all();
        $query = Purchase::query();

        // Apply filters if provided
        if (isset($filters['item_name'])) {
            $query->where('item_name', 'like', '%' . $filters['item_name'] . '%');
        }

        // Eager load the itemCategory relationship
        $purchases = $query->with('itemCategory')->get();

        // Debugging statements
        foreach ($purchases as $purchase) {
            if (!$purchase->itemCategory) {
                \Log::info('ItemCategory not found for purchase_id: ' . $purchase->purchase_id);
            }
        }

        // Map the purchases to include necessary data
        $result = $purchases->map(function($purchase) {
            return [
                'purchase_id' => $purchase->purchase_id,
                'item_name' => $purchase->item_name, // Directly from purchases table
                'itemcategory' => $purchase->itemCategory ? $purchase->itemCategory->item_category : 'N/A',
                'quantity_purchased' => $purchase->quantity_purchased,
                'price' => $purchase->price,
                'purchase_date' => $purchase->purchase_date,
                'status' => $purchase->status,
            ];
        });

        return response()->json($result, Response::HTTP_OK);
    }


    // Get a single purchase by ID
    // Get a single purchase by purchase_id
public function show($purchase_id)
{
    try {
        $purchase = Purchase::where('purchase_id', $purchase_id)->firstOrFail(); // Retrieve by purchase_id
        return response()->json($purchase, Response::HTTP_OK); // Return the purchase data
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Purchase not found'], Response::HTTP_NOT_FOUND); // Handle if not found
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve purchase'], Response::HTTP_INTERNAL_SERVER_ERROR); // Handle other errors
    }
}


   public function store(Request $request)
    {
        // Validate incoming data
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'itemcategory_id' => 'required|integer|exists:item_categories,itemcategory_id',
            'quantity_purchased' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date_format:Y-m-d',
            'status' => 'required|string|in:Active,Not Active',
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Prepare purchase data with the authenticated user's ID
        $purchaseData = array_merge($validatedData, [
            'user_id' => Auth::id()
        ]);

        try {
            $purchase = Purchase::create($purchaseData);
            return response()->json($purchase, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Failed to create purchase: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create purchase'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update an existing purchase
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'item_name' => 'required',
            'quantity_purchased' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date_format:Y-m-d',
            'status' => 'required|string',
        ]);

        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->update($validatedData);
            return response()->json($purchase, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Purchase not found'], Response::HTTP_NOT_FOUND);
        }
    }

    // Delete a purchase
    public function destroy($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->delete();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Purchase not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function totalCount()
{
    try {
        // Count the total number of purchases for all users
        $count = Purchase::count();

        return response()->json(['total_purchases' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve purchase count'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


public function filterByDateRange(Request $request)
{
    $validatedData = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    try {
        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];

        $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
                             ->orderBy('purchase_date')
                             ->with('itemCategory')
                             ->get();

        if ($purchases->isEmpty()) {
            return response()->json(['message' => 'No purchases found in the specified date range'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($purchases, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to filter purchases by date range'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function searchByItemName(Request $request)
{
    $validatedData = $request->validate([
        'item_name' => 'required|string|max:255',
    ]);

    try {
        $purchases = Purchase::where('item_name', 'like', '%' . $validatedData['item_name'] . '%')
                             ->orderBy('purchase_date')
                             ->with('itemCategory')
                             ->get();

        if ($purchases->isEmpty()) {
            return response()->json(['message' => 'No purchases found with the specified item name'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($purchases, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to search purchases'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



    public function purchaseCount()
{
    try {
        $count = Purchase::count();
        return response()->json(['total_purchases' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve purchase count'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
