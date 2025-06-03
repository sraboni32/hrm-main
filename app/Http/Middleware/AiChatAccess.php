<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access AI Chat');
        }

        // Check if user has AI chat access based on role
        if (!$this->hasAiChatAccess($user)) {
            return redirect()->back()->with('error', 'You do not have permission to access AI Chat');
        }

        return $next($request);
    }

    /**
     * Check if user has AI chat access
     */
    private function hasAiChatAccess($user)
    {
        // Super Admin always has access
        if ($user->role_users_id == 1) {
            return true;
        }

        // Check specific permissions
        if ($user->can('ai_chat_access')) {
            return true;
        }

        // Default roles that have access
        $allowedRoles = [1, 2]; // Super Admin, Employee
        
        return in_array($user->role_users_id, $allowedRoles);
    }
}
