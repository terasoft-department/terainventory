<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    // Get all items
     public function index()
    {
        try {
            // Eager load the itemCategory relationship
            $items = Item::with('itemCategory')->get();

            return response()->json($items, Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error retrieving items: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve items'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


public function show($id)
{
    try {
        // Eager load only the itemCategory relationship
        $item = Item::with('itemCategory')->findOrFail($id);

        // Return the item with a successful status code
        return response()->json($item, Response::HTTP_OK);
    } catch (ModelNotFoundException $e) {
        // Log the error for debugging
        \Log::error('Item not found: ' . $e->getMessage());

        // Return a JSON response with a not found status code
        return response()->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        // Log the general error for debugging
        \Log::error('Failed to retrieve item: ' . $e->getMessage());

        // Return a JSON response with an internal server error status code
        return response()->json(['error' => 'Failed to retrieve item'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    // Create a new item
  public function store(Request $request)
{
    // Ensure the user is authenticated
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    // Validate the incoming request data
    $validatedData = $request->validate([
        'item_name' => 'required|string|max:255',
        'itemcategory_id' => [
            'required',
            'integer',
            function ($attribute, $value, $fail) {
                if (!ItemCategory::find($value)) {
                    $fail('The selected item category is invalid.');
                }
            },
        ],
        'quantity' => 'required|numeric|min:0',
        'price' => 'required|numeric|min:0',
        'status' => 'required|string|in:Active,Not-active',
        'item_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Handle the image upload
        $imagePath = null;
        if ($request->hasFile('item_img')) {
            $image = $request->file('item_img');
            $imageName = $image->getClientOriginalName(); // Use the original filename
            $image->move(public_path('images'), $imageName); // Move the image to the public/images directory
            $imagePath = 'images/' . $imageName; // Store the path relative to the public directory
        }

        // Merge the user_id with the validated data
        $itemData = array_merge($validatedData, ['item_img' => $imagePath, 'user_id' => Auth::id()]);

        // Create the item
        $item = Item::create($itemData);

        return response()->json($item, Response::HTTP_CREATED);
    } catch (\Exception $e) {
        \Log::error('Failed to create item: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create item'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


   //distribute item route
  public function update(Request $request, $id)
{
    // Validate the amount_distributed and distribution attributes
    $validatedData = $request->validate([
        'amount_distributed' => 'required|integer|min:0',
        'distribution' => 'required', // Validate the distribution field
    ]);

    try {
        // Find the item by its ID
        $item = Item::findOrFail($id);

        $currentQuantity = (int) $item->quantity; // Cast the current quantity to an integer

        // Check if there is enough stock available
        if ($validatedData['amount_distributed'] <= $currentQuantity) {
            $newQuantity = $currentQuantity - $validatedData['amount_distributed']; // Calculate the new quantity

            // Update the item with the new quantity and the amount distributed
            $item->update([
                'quantity' => $newQuantity,
                'amount_distributed' => (int) $item->amount_distributed + (int) $validatedData['amount_distributed'], // Cast to integer before addition
                'distribution' => $validatedData['distribution'], // Update distribution location
            ]);

            // Return the updated item
            return response()->json($item, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Item not found
        return response()->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        // Handle other errors
        return response()->json(['error' => 'Failed to update item'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



    // Delete an item
    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);

            // Delete the image if it exists
            if ($item->item_img && file_exists(public_path($item->item_img))) {
                unlink(public_path($item->item_img));
            }

            $item->delete();
            return response()->json(['message' => 'Item deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete item'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Get total count of items for all authenticated users
public function totalCount()
{
    try {
        // Count the total number of items for all users
        $count = Item::count();

        return response()->json(['total_items' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve item count'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



    // Filter items by date range
public function filterByDateRangev2(Request $request)
{
    // Validate the date range parameters
    $validatedData = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    try {
        // Extract start and end dates from validated data
        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];

        // Filter items by date range for any user
        $items = Item::whereBetween('created_at', [$startDate, $endDate])
                     ->orderBy('item_id')
                     ->with(['itemCategory', 'store']) // Exclude 'user' relationship
                     ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found in the specified date range'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($items, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to filter items by date range'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


// Search items by item_name
public function searchByNamev2(Request $request)
{
    // Validate the search term
    $validatedData = $request->validate([
        'item_name' => 'required|string|max:255',
    ]);

    try {
        // Search for items by item_name for any user
        $items = Item::where('item_name', 'like', '%' . $validatedData['item_name'] . '%')
                     ->orderBy('item_id')
                     ->with(['itemCategory', 'store']) // Exclude 'user' relationship
                     ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($items, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to search items'], Response::HTTP_INTERNAL_SERVER_ERROR);
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

        $items = Item::whereBetween('created_at', [$startDate, $endDate])
                     ->orderBy('created_at')
                     ->with(['itemCategory', 'store'])
                     ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found in the specified date range'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($items, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to filter items by date range'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function searchByName(Request $request)
{
    $validatedData = $request->validate([
        'item_name' => 'required|string|max:255',
    ]);

    try {
        $items = Item::where('item_name', 'like', '%' . $validatedData['item_name'] . '%')
                     ->orderBy('item_name')
                     ->with(['itemCategory', 'store'])
                     ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found with the specified name'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($items, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to search items'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


public function countItemsWithDistribution()
{
    try {
        // Count the number of items where the 'distribution' field is not null
        $count = Item::whereNotNull('distribution')->count();

        return response()->json(['count_with_distribution' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve item count with distribution'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


public function countAllItems()
{
    try {
        \Log::info('countAllItems method called');
        $count = Item::count();
        return response()->json(['total_items' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        \Log::error('Failed to retrieve item count: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to retrieve item count'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



}
