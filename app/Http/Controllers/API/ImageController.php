<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', []);
    }

    public function getImages()
    {
        $publicPath = public_path('ndvi');

        $imagePath = $publicPath . '/**/*.{jpg,jpeg,png,gif}';
        $images = glob($imagePath, GLOB_BRACE);
        if (empty($images)) {
            return response()->json([]);
        }

        $data = [];
        foreach ($images as $image) {
            $file = pathinfo($image);
            $data[] = [
                'name' => $file['basename'],
                'image_url' => asset(str_replace($publicPath, '', $image)),
            ];
        }

        return response()->json([
            'status'    => true,
            'message'   => 'success',
            'data'      => $data
        ]);
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
