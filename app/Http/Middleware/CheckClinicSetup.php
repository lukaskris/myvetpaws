<?php
// app/Http/Middleware/CheckClinicSetup.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClinicSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for auth routes and if user already has a clinic
        if (
            $request->is('admin/auth/*') ||
            $request->routeIs('filament.admin.pages.setup-clinic') ||
            ($user && $user->clinic_id)
        ) {
            return $next($request);
        }

        // Redirect to setup if authenticated but no clinic
        if ($user && !$user->clinic_id) {
            return redirect()->route('filament.admin.pages.setup-clinic');
        }

        return $next($request);
    }
}
