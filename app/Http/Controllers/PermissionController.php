<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermissionController extends Controller
{
    // Get all permissions
    public function index()
    {
        try {
            $permissions = Permission::all();
            return response()->json($permissions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve permissions'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Get a single permission by ID
    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            return response()->json($permission, Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Create a new permission
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'permission_description' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,role_id',
        ]);

        try {
            $permission = Permission::create($validatedData);
            return response()->json($permission, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update an existing permission
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'permission_description' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,role_id',
        ]);

        try {
            $permission = Permission::findOrFail($id);
            $permission->update($validatedData);
            return response()->json($permission, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete a permission
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();
            return response()->json(['message' => 'Permission deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
