<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Utils\AuditLogger;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('asset_tag', 'ilike', "%{$search}%")
                    ->orWhere('serial_number', 'ilike', "%{$search}%")
                    ->orWhere('department', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = $request->get('limit', 15);
        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serial_number' => 'nullable|string',
            'asset_tag' => 'nullable|string',
            'status' => 'required|string',
            'department' => 'nullable|string',
            'location' => 'nullable|string',
            'brand' => 'nullable|string',
            'model_name' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

        // Invalidate inventory cache
        \Illuminate\Support\Facades\Cache::flush();

        AuditLogger::log('Inventory', 'Created', "Added new inventory item: {$item->name} ({$item->type}).");

        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        return response()->json(InventoryItem::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $oldData = $item->toArray();

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serial_number' => 'nullable|string',
            'asset_tag' => 'nullable|string',
            'status' => 'required|string',
            'department' => 'nullable|string',
            'location' => 'nullable|string',
            'brand' => 'nullable|string',
            'model_name' => 'nullable|string',
            'color' => 'nullable|string',
            'last_audit_date' => 'nullable|date',
        ]);

        $item->update($validated);
        $newData = $item->fresh()->toArray();

        // Invalidate inventory cache
        \Illuminate\Support\Facades\Cache::flush();

        AuditLogger::log('Inventory', 'Updated', "Updated inventory item: {$item->name}.", $oldData, $newData);

        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $itemName = $item->name;
        $item->delete();

        // Invalidate inventory cache
        \Illuminate\Support\Facades\Cache::flush();

        AuditLogger::log('Inventory', 'Deleted', "Removed inventory item: {$itemName}.");

        return response()->json(['message' => 'Item deleted']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\InventoryImport, $request->file('file'));

            // Invalidate inventory cache
            \Illuminate\Support\Facades\Cache::flush();

            // Audit Log
            AuditLogger::log('Inventory', 'Imported', "Imported system inventory assets via Excel file.");

            return response()->json(['message' => 'Import successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }
}
