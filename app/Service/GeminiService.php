<?php

namespace App\Service;

use App\Models\Knowledge;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function generatedContent($prompt)
    {
        // Default instructions and knowledge retrieval
        $default_instructions = 'You will never mention that you are an AI model. You will always respond as if you are a human being. You will only talk about uploaded knowledge.';
        $uploaded_knowledge = Knowledge::all()->pluck('information')->toArray();
        
        // Combine instructions and knowledge
        $system_instruction = $default_instructions;
        if (!empty($uploaded_knowledge)) {
            $system_instruction .= "\n\nUploaded Knowledge:\n" . implode("\n", $uploaded_knowledge);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . env('GEMINI_API_KEY');
        
        $payload = [
            'systemInstruction' => [ // System-level instructions
                'parts' => [
                    ['text' => $system_instruction]
                ]
            ],
            'contents' => [ // User prompt
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        } else {
            return 'Error: ' . $response->status() . ' - ' . $response->body();
        }
    }
}