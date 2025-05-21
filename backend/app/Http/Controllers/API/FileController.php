<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,docx,txt,jpg,jpeg,png',
            'type' => [
                'required',
                'in:avatar,deal,comment',
                Rule::when(
                    $request->user()->getRole() === 'client' && $request->input('type') === 'avatar',
                    'in:avatar,deal',
                    'in:avatar,comment'
                )
            ]
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $userId = $request->user()->getId();

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("public/{$type}s", $filename);

        return response()->json([
            'message' => 'Файл успешно загружен',
            'url' => Storage::url($path),
            'path' => $path,
            'filename' => $filename
        ]);
    }

    public function download(string $type, string $filename): JsonResponse
    {
        $path = "public/{$type}s/{$filename}";

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Файл не найден'], 404);
        }

        return response()->json(['url' => Storage::url($path)]);
    }

    public function delete(string $type, string $filename): JsonResponse
    {
        $path = "public/{$type}s/{$filename}";

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Файл не найден'], 404);
        }

        Storage::delete($path);
        return response()->json(['message' => 'Файл удален успешно']);
    }
}
