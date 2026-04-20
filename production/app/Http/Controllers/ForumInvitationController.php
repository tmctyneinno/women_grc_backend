<?php

namespace App\Http\Controllers;

use App\Models\ForumInvitation;
use App\Models\ForumMembership;
use Illuminate\Http\Request;

class ForumInvitationController extends Controller
{
    public function accept(string $token)
    {
        $invitation = ForumInvitation::where('token', $token)->first();
        if (!$invitation) {
            abort(404);
        }

        if ($invitation->status !== 'pending') {
            return redirect()->to(config('app.frontend_url', env('FRONTEND_URL', '/')) . '/account/forum/' . $invitation->forum_id);
        }

        ForumMembership::updateOrCreate(
            ['forum_id' => $invitation->forum_id, 'user_id' => $invitation->invited_user_id],
            ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
        );

        $invitation->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return redirect()->to(config('app.frontend_url', env('FRONTEND_URL', '/')) . '/account/forum/' . $invitation->forum_id . '?invite=accepted');
    }
}
