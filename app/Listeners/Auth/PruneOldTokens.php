<?php

namespace App\Listeners\Auth;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class PruneOldTokens
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AccessTokenCreated $event): void
    {
        $user = $event->userId;
        $clientId = $event->clientId;

        // Check if the user is master admin (ID 1)
        if($user == 1) return;

        // Revoke all previous tokens for the user and client
        $tokens = Token::where('user_id', $user)
            ->where('client_id', $clientId)
            ->where('updated_at', '<', now()) // Adjust the time period as needed
            ->get();

        if(count($tokens) > 0) {
            foreach ($tokens as $token) {
                $token->revoke();
                $token->delete();
            }
        }
    }
}
