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
    public static function log($module, $action, $description, $oldData = null, $newData = null, $userId = null)
    {
        $userAgent = Request::header('User-Agent');
        $device = 'Unknown Device';
        $finalUserId = $userId ?? Auth::id();

        if ($userAgent) {
            $os = 'Unknown OS';
            if (stripos($userAgent, 'windows') !== false)
                $os = 'Windows PC';
            elseif (stripos($userAgent, 'macintosh') !== false || stripos($userAgent, 'mac os x') !== false)
                $os = 'Mac';
            elseif (stripos($userAgent, 'android') !== false)
                $os = 'Android';
            elseif (stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false)
                $os = 'iOS Device';
            elseif (stripos($userAgent, 'linux') !== false)
                $os = 'Linux PC';

            $browser = 'Unknown Browser';
            if (stripos($userAgent, 'edg') !== false)
                $browser = 'Edge';
            elseif (stripos($userAgent, 'chrome') !== false)
                $browser = 'Chrome';
            elseif (stripos($userAgent, 'safari') !== false)
                $browser = 'Safari';
            elseif (stripos($userAgent, 'firefox') !== false)
                $browser = 'Firefox';
            elseif (stripos($userAgent, 'opr') !== false || stripos($userAgent, 'opera') !== false)
                $browser = 'Opera';

            $device = "{$os} ({$browser})";
        }

        SystemLog::create([
            'user_id' => $finalUserId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => Request::ip(),
            'user_agent' => $userAgent,
            'device' => $device,
            'location' => 'Local Network Access'
        ]);
    }
}
