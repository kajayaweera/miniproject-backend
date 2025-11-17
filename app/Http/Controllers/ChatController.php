<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    private $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Send a message to the chatbot
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'array'
        ]);

        $userMessage = $request->input('message');
        $conversationHistory = $request->input('history', []);

        try {
            // Get bot response
            $botResponse = $this->chatbotService->getResponse($userMessage, $conversationHistory);

            return response()->json([
                'success' => true,
                'response' => $botResponse,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I\'m having trouble right now. Please try again later.',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get chat history (not needed anymore since using localStorage)
     */
    public function getChatHistory(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Chat history is now stored locally in your browser'
        ]);
    }

    /**
     * Show chat widget (for testing purposes)
     */
    
}
