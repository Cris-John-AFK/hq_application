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

    public function export(Request $request)
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

        $items = $query->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=organization_inventory_' . now()->format('Y-m-d_His') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Asset Tag',
            'Serial Number',
            'Type',
            'Nomenclature (Name)',
            'Brand',
            'Model',
            'Color',
            'Department',
            'Location',
            'Status',
            'Quantity',
            'Added Date'
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->asset_tag ?? '--',
                    $item->serial_number ?? '--',
                    $item->type ?? '--',
                    $item->name ?? '--',
                    $item->brand ?? '--',
                    $item->model_name ?? '--',
                    $item->color ?? '--',
                    $item->department ?? '--',
                    $item->location ?? '--',
                    $item->status ?? '--',
                    $item->quantity ?? '1',
                    \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        AuditLogger::log('Inventory', 'Exported', "Exported organization inventory to CSV.");

        return response()->stream($callback, 200, $headers);
    }
}
