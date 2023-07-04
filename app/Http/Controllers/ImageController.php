<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

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
}
