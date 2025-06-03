<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestLaravelHttp extends Command
{
    protected $signature = 'test:laravel-http';
    protected $description = 'Test NVIDIA API using Laravel HTTP client (same as chat service)';

    public function handle()
    {
        $this->info('🔍 Testing Laravel HTTP Client Integration...');
        $this->newLine();

        $apiKey = config('services.nvidia_ai.api_key');
        $baseUrl = config('services.nvidia_ai.base_url', 'https://integrate.api.nvidia.com/v1');
        $apiUrl = rtrim($baseUrl, '/') . '/chat/completions';
        $model = config('services.nvidia_ai.model', 'meta/llama-3.3-70b-instruct');

        $this->info("🔑 API Key: " . substr($apiKey, 0, 20) . "...");
        $this->info("🌐 API URL: {$apiUrl}");
        $this->info("🤖 Model: {$model}");
        $this->newLine();

        // Test the exact same way as AiChatService
        try {
            $this->info('📡 Testing with Laravel HTTP client (same as chat service)...');
            
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful HR assistant. Respond briefly and professionally.'
                ],
                [
                    'role' => 'user',
                    'content' => 'Hello! Can you help me with HR questions?'
                ]
            ];

            $this->info('📝 Request payload:');
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1024,
                'temperature' => 0.2,
                'top_p' => 0.7,
                'stream' => false
            ];
            $this->line(json_encode($payload, JSON_PRETTY_PRINT));
            $this->newLine();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(60)->post($apiUrl, $payload);

            $this->info('📊 Response Status: ' . $response->status());
            $this->info('📄 Response Headers:');
            foreach ($response->headers() as $key => $value) {
                $this->line("   {$key}: " . (is_array($value) ? implode(', ', $value) : $value));
            }
            $this->newLine();

            if ($response->successful()) {
                $data = $response->json();
                
                $this->info('✅ Laravel HTTP Request Successful!');
                $this->newLine();
                
                $this->info('📄 Full Response:');
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
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
                $this->info('🎉 Laravel HTTP client is working with NVIDIA API!');
                $this->info('The issue must be elsewhere in the chat service...');
                
                return 0;
                
            } else {
                $this->error('❌ Laravel HTTP Request Failed');
                $this->error('Status: ' . $response->status());
                $this->error('Body: ' . $response->body());
                
                // Log the full response for debugging
                Log::error('Laravel HTTP Test Failed', [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body()
                ]);
                
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception in Laravel HTTP test:');
            $this->error($e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            
            Log::error('Laravel HTTP Test Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
