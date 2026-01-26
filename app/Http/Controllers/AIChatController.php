<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\AIChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIChatController extends Controller
{
    protected $aiService;

    public function __construct(AIChatService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // 1. Save User Message
        $userMsg = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => 'user'
        ]);

        // 2. Generate AI Response
        $aiResult = $this->aiService->generateResponse($user, $request->message);

        // 3. Save AI Message
        $aiMsg = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $aiResult['text'],
            'type' => 'ai',
            'context_data' => $aiResult['context']
        ]);

        return response()->json([
            'user_message' => $userMsg,
            'ai_message' => $aiMsg
        ]);
    }

    public function clear()
    {
        ChatMessage::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Chat history cleared']);
    }
}
