<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemLogsController extends Controller
{
    public function index()
    {
        return \App\Models\SystemLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(100);
    }
}
