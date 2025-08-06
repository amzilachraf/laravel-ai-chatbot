<?php

namespace App\Http\Controllers;

use App\Service\GeminiService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function generate(Request $request, GeminiService $gemini)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $prompt = $request->message;
        $response = $gemini->generatedContent($prompt);
        
        return response()->json($response);
    }
}