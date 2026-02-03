<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Utils\AuditLogger;

class InventoryController extends Controller
{
    public function index()
    {
        return response()->json(InventoryItem::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serial_number' => 'nullable|string',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $item = InventoryItem::create($validated);

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
            'status' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'last_audit_date' => 'nullable|date',
        ]);

        $item->update($validated);
        $newData = $item->fresh()->toArray();

        AuditLogger::log('Inventory', 'Updated', "Updated inventory item: {$item->name}.", $oldData, $newData);

        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $itemName = $item->name;
        $item->delete();

        AuditLogger::log('Inventory', 'Deleted', "Removed inventory item: {$itemName}.");

        return response()->json(['message' => 'Item deleted']);
    }
}
