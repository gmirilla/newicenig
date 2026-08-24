<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $page = Page::where('slug', 'about')->where('is_published', true)->first();

        return view('marketing.about', ['page' => $page]);
    }

    public function leadership(): View
    {
        $teamMembers = TeamMember::where('is_current', true)
            ->orderBy('display_order')
            ->get();

        return view('marketing.leadership', ['teamMembers' => $teamMembers]);
    }

    public function membership(): View
    {
        return view('marketing.membership');
    }
}
