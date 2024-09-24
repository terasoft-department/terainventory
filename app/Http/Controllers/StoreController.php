<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreController extends Controller
{
    // Get all stores
    public function index()
    {
        try {
            $stores = Store::all();
            return response()->json($stores, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve stores'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Get a single store by ID
    public function show($id)
    {
        try {
            $store = Store::findOrFail($id);
            return response()->json($store, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Store not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve store'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Create a new store
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'store_category' => 'required|string|max:255',
        'location' => 'required|string|max:255',
    ]);

    try {
        $store = Store::create($validatedData);
        return response()->json($store, Response::HTTP_CREATED);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to create store'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    // Update an existing store
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'store_category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        try {
            $store = Store::findOrFail($id);
            $store->update($validatedData);
            return response()->json($store, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Store not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update store'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete a store
    public function destroy($id)
    {
        try {
            $store = Store::findOrFail($id);
            $store->delete();
            return response()->json(['message' => 'Store deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Store not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete store'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
