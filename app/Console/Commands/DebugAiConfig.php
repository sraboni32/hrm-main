<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugAiConfig extends Command
{
    protected $signature = 'debug:ai-config';
    protected $description = 'Debug AI configuration values';

    public function handle()
    {
        $this->info('🔍 AI Configuration Debug');
        $this->newLine();

        // Check NVIDIA config
        $this->info('📡 NVIDIA Configuration:');
        $this->line('API Key: ' . (config('services.nvidia_ai.api_key') ? 'SET (' . substr(config('services.nvidia_ai.api_key'), 0, 20) . '...)' : 'NOT SET'));
        $this->line('Base URL: ' . (config('services.nvidia_ai.base_url') ?: 'NOT SET'));
        $this->line('Model: ' . (config('services.nvidia_ai.model') ?: 'NOT SET'));
        $this->line('Max Tokens: ' . (config('services.nvidia_ai.max_tokens') ?: 'NOT SET'));
        $this->line('Temperature: ' . (config('services.nvidia_ai.temperature') ?: 'NOT SET'));
        $this->line('Top P: ' . (config('services.nvidia_ai.top_p') ?: 'NOT SET'));
        $this->newLine();

        // Check OpenAI config
        $this->info('🤖 OpenAI Configuration (Fallback):');
        $this->line('API Key: ' . (config('services.openai.api_key') ? 'SET (' . substr(config('services.openai.api_key'), 0, 20) . '...)' : 'NOT SET'));
        $this->line('Model: ' . (config('services.openai.model') ?: 'NOT SET'));
        $this->line('Max Tokens: ' . (config('services.openai.max_tokens') ?: 'NOT SET'));
        $this->line('Temperature: ' . (config('services.openai.temperature') ?: 'NOT SET'));
        $this->newLine();

        // Check environment variables directly
        $this->info('🌍 Environment Variables:');
        $this->line('NVIDIA_API_KEY: ' . (env('NVIDIA_API_KEY') ? 'SET (' . substr(env('NVIDIA_API_KEY'), 0, 20) . '...)' : 'NOT SET'));
        $this->line('NVIDIA_BASE_URL: ' . (env('NVIDIA_BASE_URL') ?: 'NOT SET'));
        $this->line('NVIDIA_MODEL: ' . (env('NVIDIA_MODEL') ?: 'NOT SET'));
        $this->line('OPENAI_API_KEY: ' . (env('OPENAI_API_KEY') ? 'SET (' . substr(env('OPENAI_API_KEY'), 0, 20) . '...)' : 'NOT SET'));
        $this->newLine();

        // Check if config is cached
        $configPath = base_path('bootstrap/cache/config.php');
        if (file_exists($configPath)) {
            $this->warn('⚠️  Configuration is cached. Run "php artisan config:clear" if you made changes.');
        } else {
            $this->info('✅ Configuration is not cached.');
        }

        $this->newLine();
        $this->info('💡 Recommendations:');
        
        if (!config('services.nvidia_ai.api_key')) {
            $this->error('❌ NVIDIA API key is missing. Add NVIDIA_API_KEY to your .env file.');
        } else {
            $this->info('✅ NVIDIA API key is configured.');
        }

        if (!config('services.nvidia_ai.base_url')) {
            $this->error('❌ NVIDIA base URL is missing. Add NVIDIA_BASE_URL to your .env file.');
        } else {
            $this->info('✅ NVIDIA base URL is configured.');
        }

        return 0;
    }
}
