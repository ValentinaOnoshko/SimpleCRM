<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function addComment(Request $request, Deal $deal): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,docx,txt,jpg,jpeg,png',
        ]);

        $data = $request->only('content');
        $data['user_id'] = $request->user()->getId();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs("public/comments", $filename);

            $data['file_path'] = Storage::url($path);
            $data['file_name'] = $filename;
            $data['file_type'] = $file->getMimeType();
        }

        $comment = $deal->comments()->create($data);
        $comment->load('user');

        return response()->json($comment, 201);
    }
}
