<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Str;

class AIChatService
{
    public function generateResponse($user, $message)
    {
        // Aggressive key detection (Config -> Env -> Fallback)
        $apiKey = config('services.gemini.key');
        if (!$apiKey) $apiKey = env('GEMINI_API_KEY');
        
        // If still no key, check if it's literally empty string
        if (empty($apiKey)) {
            return $this->getFallbackResponse($user, $message);
        }
        $totalEmployees = User::count();
        $onLeaveToday = LeaveRequest::where('status', 'Approved')
            ->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now())
            ->count();
        $pendingRequests = LeaveRequest::where('status', 'Pending')->count();
        $userBalance = $user->leave_credits;

        $systemPrompt = "You are the HatQ Management System AI. You have access to real-time company data. 
        Current System State:
        - Active Employees: {$totalEmployees}
        - Staff currently on leave: {$onLeaveToday}
        - Requests pending approval: {$pendingRequests}
        - User talking to you: {$user->name} (Role: {$user->role})
        - This user's SIL Balance: {$userBalance} days.
        
        Rules:
        1. Be professional, helpful, and concise. 
        2. If the user is an employee, do not reveal details about other employees, only general stats.
        3. If they ask about navigation, suggest the left sidebar.
        4. Use bold text for numbers and key terms.
        
        User Message: \"{$message}\"";

        // 2. If no API Key, use the legacy logic (Smart Fallback)
        if (! $apiKey) {
            return $this->getFallbackResponse($user, $message);
        }

        // 3. Call Real AI (Gemini)
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');

                return [
                    'text' => $aiText,
                    'context' => 'gemini-api',
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini API Error: '.$e->getMessage());
        }

        return $this->getFallbackResponse($user, $message);
    }

    private function getFallbackResponse($user, $message)
    {
        $message = Str::lower(trim($message));
        // ... (Keep the previous logic here as a safety net)
        if (Str::contains($message, ['balance', 'credits'])) {
            return ['text' => "I'm currently in 'Safety Mode' (Missing API Key). Your SIL balance is **{$user->leave_credits} days**.", 'context' => null];
        }

        return ['text' => "I am initialized but my Neural Link (API Key) is missing. Set 'GEMINI_API_KEY' in your .env to enable full AI. For now, I can only tell you your balance is **{$user->leave_credits}**.", 'context' => null];
    }
}
