<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'image' => 'required|image|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('images', 'public');

                $imageData = [
                    'path' => $path,
                    'user_id' => $user->id,
                    'mime_type' => $image->getClientMimeType(),
                ];

                $uploadedImage = Image::create($imageData);

                return response()->json(['message' => 'Image uploaded successfully', 'image' => $uploadedImage], Response::HTTP_OK);
            }

            return response()->json(['message' => 'Image upload failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(int $image_id)
    {
        try {
            $image = Image::findOrFail($image_id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Image not found'], Response::HTTP_NOT_FOUND);
        }

        $path = $image->path;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Image file not found'], Response::HTTP_NOT_FOUND);
        }

        $imageContents = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($imageContents, Response::HTTP_OK)->header('Content-Type', $mimeType);
    }
}
