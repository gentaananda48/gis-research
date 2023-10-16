<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use File;

class ImageController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', []);
    }

    public function getImages()
    {
        $folderName = 'ndvi';
        $publicPath = public_path($folderName);
        $imagePath = $publicPath . '/{**/*,*}';

        $images = File::glob($imagePath, GLOB_BRACE);

        if (empty($images)) {
            return response()->json([]);
        }

        $data = [];
        foreach ($images as $image) {
            $file = pathinfo($image);

            $data[] = [
                'name' => $file['basename'],
                'image_url' => asset(str_replace($publicPath, '/' . $folderName, $image)),
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
        // return Auth::guard('api');
    }
}
