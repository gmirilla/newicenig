<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MemberDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MemberDocumentController extends Controller
{
    public function show(Request $request, MemberDocument $document): Response
    {
        $user = $request->user();

        abort_unless(
            $document->user_id === $user->id || $user->hasAnyRole(User::PANEL_ROLES),
            403
        );

        $media = $document->getFirstMedia('file');

        abort_unless($media, 404);

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
