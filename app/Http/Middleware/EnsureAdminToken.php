<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminToken
{
    /**
     * The name of the admin token.
     *
     * @var string
     */
    protected string $admin_token_name = 'admin_token';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if token is present in the request
        if (!$request->hasHeader('Authorization')) {
            return response()->json([
                'message' => 'Authorization header is missing.'
            ], 401);
        }

        // Extract the token from the Authorization header
        $auth_header = $request->header('Authorization');
        $token_parts = explode(' ', $auth_header);

        if (count($token_parts) !== 2 || $token_parts[0] !== 'Bearer') {
            return response()->json([
                'message' => 'Invalid Authorization header format.'
            ], 400);
        }

        // Retrieve the token from the authenticated user and check its name
        $user = $request->user();
        $token = $request->bearerToken();
        $token_name = $user->token()->name;
        
        if (!$user || !$token || $token_name !== $this->admin_token_name) {
            return response()->json([
                'message' => 'Unauthorized access. Admin token is required.'
            ], 403);
        }

        app()->instance('client', 'admin');

        return $next($request);
    }
}
