<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\View\View;

class DownloadController extends Controller
{
    public function index(): View
    {
        $downloads = Download::where('is_public', true)
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        return view('marketing.resources.index', ['downloads' => $downloads]);
    }
}
