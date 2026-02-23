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
        $userAgent = Request::header('User-Agent');
        $device = 'Unknown Device';

        if ($userAgent) {
            if (stripos($userAgent, 'mobi') !== false)
                $device = 'Mobile Device';
            elseif (stripos($userAgent, 'tablet') !== false)
                $device = 'Tablet';
            elseif (stripos($userAgent, 'windows') !== false)
                $device = 'Windows PC';
            elseif (stripos($userAgent, 'macintosh') !== false)
                $device = 'Mac';
            elseif (stripos($userAgent, 'linux') !== false)
                $device = 'Linux PC';
        }

        SystemLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => Request::ip(),
            'user_agent' => $userAgent,
            'device' => $device,
            'location' => 'PH (Local Network)' // Placeholder for location since we are in local dev
        ]);
    }
}
