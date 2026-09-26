<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectWrongPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            
            // Jika dia GURU tapi mencoba masuk ke link Admin, lempar ke panel Teacher
            if ($role === 'teacher' && $request->is('admin*')) {
                return redirect('/teacher');
            }
            
            // Jika dia ADMIN tapi mencoba masuk ke link Guru, lempar ke panel Admin
            if ($role === 'admin' && $request->is('teacher*')) {
                return redirect('/admin');
            }
        }
        
        return $next($request);
    }
}