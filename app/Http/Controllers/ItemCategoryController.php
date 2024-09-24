<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemCategoryController extends Controller
{
    // Get all item categories
     public function index()
{
    try {
        $itemCategories = ItemCategory::all();
        return response()->json($itemCategories, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve item categories'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


     // Get a single item category by ID
    public function show($id)
    {
        try {
            $itemCategory = ItemCategory::findOrFail($id);
            return response()->json($itemCategory, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Item category not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve item category'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Create a new item category
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_category' => 'required|string|max:255',
        ]);

        try {
            $itemCategory = ItemCategory::create($validatedData);
            return response()->json($itemCategory, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create item category'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update an existing item category
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'item_category' => 'required|string|max:255',
        ]);

        try {
            $itemCategory = ItemCategory::findOrFail($id);
            $itemCategory->update($validatedData);
            return response()->json($itemCategory, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Item category not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item category'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete an item category
    public function destroy($id)
    {
        try {
            $itemCategory = ItemCategory::findOrFail($id);
            $itemCategory->delete();
            return response()->json(['message' => 'Item category deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Item category not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete item category'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Get item categories for the logged-in user
    public function userCategories()
    {
        try {
            $userId = auth()->id(); // Get the logged-in user's ID

            // Fetch item categories associated with the logged-in user
            $itemCategories = ItemCategory::where('user_id', $userId)->get();

            return response()->json($itemCategories, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve user-specific item categories'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Get the count of all item categories
public function countCategory()
{
    try {
        $count = ItemCategory::count(); // Get the count of item categories

        return response()->json(['count' => $count], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve item category count'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
