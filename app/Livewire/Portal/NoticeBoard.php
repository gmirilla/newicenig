<?php

namespace App\Livewire\Portal;

use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NoticeBoard extends Component
{
    public function getNoticesProperty()
    {
        $membershipTierId = Auth::user()->currentMembership()?->membership_tier_id;

        return Notice::visible()
            ->with('membershipTiers')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->get()
            ->filter(fn (Notice $notice) => $notice->isVisibleToTier($membershipTierId));
    }

    public function render()
    {
        return view('livewire.portal.notice-board');
    }
}
