<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemLogsController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\SystemLog::with('user');

        // Search in description, ip, or user name
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Module (Case-insensitive)
        if ($request->filled('module')) {
            $query->where('module', 'ILIKE', $request->get('module'));
        }

        // Filter by Action (Case-insensitive)
        if ($request->filled('action')) {
            $query->where('action', 'ILIKE', $request->get('action'));
        }

        // Filter by specific Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->get('date'));
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
    }
}
