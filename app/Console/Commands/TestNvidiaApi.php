<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestNvidiaApi extends Command
{
    protected $signature = 'test:nvidia-api';
    protected $description = 'Test NVIDIA API connection and response';

    public function handle()
    {
        $this->info('🚀 Testing NVIDIA API Connection...');
        $this->newLine();

        $apiKey = config('services.nvidia_ai.api_key');
        $baseUrl = config('services.nvidia_ai.base_url');
        $model = config('services.nvidia_ai.model');

        if (!$apiKey) {
            $this->error('❌ NVIDIA_API_KEY not found in configuration');
            $this->info('Please add NVIDIA_API_KEY to your .env file');
            return 1;
        }

        $this->info("🔑 API Key: " . substr($apiKey, 0, 20) . "...");
        $this->info("🌐 Base URL: {$baseUrl}");
        $this->info("🤖 Model: {$model}");
        $this->newLine();

        try {
            $this->info('📡 Sending test request...');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful HR assistant. Respond briefly and professionally.'
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Hello! Can you help me with HR questions?'
                    ]
                ],
                'max_tokens' => 100,
                'temperature' => 0.2,
                'top_p' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $this->info('✅ API Connection Successful!');
                $this->newLine();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $content = $data['choices'][0]['message']['content'];
                    $this->info('🤖 AI Response:');
                    $this->line($content);
                    $this->newLine();
                }
                
                if (isset($data['usage'])) {
                    $usage = $data['usage'];
                    $this->info('📊 Token Usage:');
                    $this->line("   Prompt tokens: " . ($usage['prompt_tokens'] ?? 'N/A'));
                    $this->line("   Completion tokens: " . ($usage['completion_tokens'] ?? 'N/A'));
                    $this->line("   Total tokens: " . ($usage['total_tokens'] ?? 'N/A'));
                }
                
                $this->newLine();
                $this->info('🎉 NVIDIA API is working perfectly!');
                $this->info('You can now use the AI HR Assistant at /ai-chat');
                
                return 0;
                
            } else {
                $this->error('❌ API Request Failed');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
