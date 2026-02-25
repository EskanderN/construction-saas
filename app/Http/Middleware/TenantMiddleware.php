<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $companyId = Auth::user()->company_id;
            
            // Добавляем глобальный скоуп для всех запросов
            $request->merge(['company_id' => $companyId]);
            
            // Для моделей будем использовать скоуп в запросах
        }

        return $next($request);
    }
}