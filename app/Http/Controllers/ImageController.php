<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetImageRequest;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::all();
        return Inertia::render(
            'ImageCropper',
            [
                'images' => $images
            ]
        );
    }
    public function store(GetImageRequest $request)
    {
        $name = uniqid() . '.png';
        $path = '/storage/upload/' . $name;
        $request->file('file')->move(public_path('/storage/upload'), $name);
        $image = Image::create([
            'title' => $path
        ]);

        return response()->json([
            'success' => 'Crop Image Saved/Uploaded Successfully using Vue and Axios In Laravel',
            'image' => $image
        ]);
    }
}
