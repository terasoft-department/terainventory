<?php
namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DarSHowRoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function index()
    {
        try {
            $items = Item::where('distribution', 'dar_showRoom')
                         ->orderBy('item_id')
                         ->with(['itemCategory', 'store', 'user'])
                         ->get();
            return response()->json($items, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve items'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     // Get a single item by item_id
     public function show($item_id)
{
    try {
        // Retrieve the item where item_id matches and distribution is Dar_showRoom
        $item = Item::where('item_id', $item_id)
                    ->where('distribution', 'Dar_showRoom')
                    ->firstOrFail();

        // Return the item data with HTTP 200 status code
        return response()->json($item, Response::HTTP_OK);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // If item not found, return a 404 response
        return response()->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        // Handle any other errors with a 500 status code
        return response()->json(['error' => 'Failed to retrieve item'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



    // Create a new item
    public function store(Request $request)
    {
        // Validate the incoming request data, excluding user_id
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'itemcategory_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'store_id' => 'required|integer',
            'status' => 'required|string',
            'item_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
        ]);

        try {
            // Get the authenticated user's ID
            $userId = auth()->id();

            // Handle the image upload
            if ($request->hasFile('item_img')) {
                $image = $request->file('item_img');
                $imageName = $image->getClientOriginalName(); // Keep the original filename
                $image->move(public_path('images'), $imageName);
                $validatedData['item_img'] = 'images/'.$imageName;
            }

            // Merge the user_id with the validated data
            $itemData = array_merge($validatedData, ['user_id' => $userId]);

            // Create the item
            $item = Item::create($itemData);

            return response()->json($item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create item'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update an existing item
   public function update(Request $request, $id)
{
    // Validate the quantity attribute
    $validatedData = $request->validate([
        'quantity' => 'required|integer|min:0',
    ]);

    try {
        $item = Item::findOrFail($id);

        $currentQuantity = $item->quantity;

        if ($validatedData['quantity'] <= $currentQuantity) {
            $newQuantity = $currentQuantity - $validatedData['quantity'];

            // Update the item with the new quantity
            $item->update(['quantity' => $newQuantity]);

            return response()->json($item, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Item not found'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
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

       // Get the count of items where distribution is 'dar_showRoom'
    public function countDarShowRoom()
    {
        try {
            // Get the authenticated user's ID
            $userId = auth()->id();

            // Count of items where distribution is 'dar_showRoom'
            $count = Item::where('user_id', $userId)
                ->where('distribution', 'dar_showRoom')
                ->count();

            return response()->json(['count_dar_showRoom' => $count], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve count for dar_showRoom'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


// Search items by item_name
  public function searchByName(Request $request)
{
    // Validate the search term
    $validatedData = $request->validate([
        'item_name' => 'required|string|max:255',
    ]);

    try {
        // Search for items by item_name
        $items = Item::where('distribution', 'dar_showRoom')
                     ->where('item_name', 'like', '%' . $validatedData['item_name'] . '%')
                     ->orderBy('item_id')
                     ->with(['itemCategory', 'store', 'user'])
                     ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($items, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to search items'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


// Filter items by date range
public function filterByDateRange(Request $request)
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

        // Filter items by date range
        $items = Item::where('distribution', 'dar_showRoom')
                     ->whereBetween('created_at', [$startDate, $endDate])
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



}
