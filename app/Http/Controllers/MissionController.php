<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mission;
use App\Models\UserMission;
use App\Models\XpTransaction;

class MissionController extends Controller
{
    public function claim(Request $request, $missionId)
    {
        $user = Auth::user();
        $todayStr = now()->format('Y-m-d');

        $mission = Mission::findOrFail($missionId);
        $userMission = UserMission::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->where('date', $todayStr)
            ->where('completed', true)
            ->whereNull('reward_claimed_at')
            ->first();

        if (!$userMission) {
            return back()->with('error', 'المهمة غير مكتملة أو تم استلام مكافئتها مسبقاً.');
        }

        $userMission->update(['reward_claimed_at' => now()]);

        XpTransaction::create([
            'user_id' => $user->id,
            'amount' => $mission->xp_reward,
            'reason' => "مكافأة المهمة اليومية: {$mission->title} 🎁",
            'reference_type' => 'MISSION_REWARD',
            'reference_id' => (string) $mission->id,
            'idempotency_key' => "MISSION_CLAIM_{$user->id}_{$mission->id}_{$todayStr}",
        ]);

        if ($user->gamificationProfile) {
            $user->gamificationProfile->increment('xp', $mission->xp_reward);
        }

        return back()->with('success', "تم استلام مكافأة المهمة (+{$mission->xp_reward} XP) بنجاح!");
    }
}
