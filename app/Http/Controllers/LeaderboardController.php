<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\GamificationProfile;

class LeaderboardController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $leaders = GamificationProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'ACTIVE');
            })
            ->orderByDesc('xp')
            ->take(50)
            ->get();

        $currentUserRank = null;
        if ($currentUser) {
            $myProfile = GamificationProfile::where('user_id', $currentUser->id)->first();
            $myScore = $myProfile ? $myProfile->xp : $currentUser->xp;
            $currentUserRank = GamificationProfile::where('xp', '>', $myScore)->count() + 1;
        }

        return view('leaderboard.index', compact('leaders', 'currentUser', 'currentUserRank'));
    }
}
