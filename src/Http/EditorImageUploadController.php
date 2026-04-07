<?php

namespace MrCatz\DataTable\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorImageUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $config = config('mrcatz.editor_image', []);
        $maxSize = $config['max_size'] ?? 2048;

        $request->validate([
            'image' => "required|image|max:{$maxSize}",
            'path'  => 'nullable|string|max:255',
        ]);

        $disk = $config['disk'] ?? 'public';
        $path = $request->input('path', $config['path'] ?? 'editor-images');

        $file = $request->file('image');
        $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs($path . '/tmp', $filename, $disk);

        $url = Storage::disk($disk)->url($storedPath);

        return response()->json(['url' => $url]);
    }
}
