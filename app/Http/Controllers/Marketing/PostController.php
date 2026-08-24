<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('marketing.news.index', ['posts' => $posts]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published, 404);

        return view('marketing.news.show', ['post' => $post]);
    }
}
