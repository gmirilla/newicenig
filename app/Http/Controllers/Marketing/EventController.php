<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $upcoming = Event::where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $past = Event::where('is_published', true)
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->paginate(9, ['*'], 'past');

        return view('marketing.events.index', ['upcoming' => $upcoming, 'past' => $past]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->is_published, 404);

        return view('marketing.events.show', ['event' => $event]);
    }
}
