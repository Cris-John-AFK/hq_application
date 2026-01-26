<?php

namespace App\Utils;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log a system action
     * 
     * @param string $module Module name (e.g., 'Leaves', 'Users')
     * @param string $action Action name (e.g., 'Created', 'Deleted')
     * @param string $description Detailed message
     * @param array|null $oldData Data before change
     * @param array|null $newData Data after change
     * @return void
     */
    public static function log($module, $action, $description, $oldData = null, $newData = null)
    {
        SystemLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent')
        ]);
    }
}
