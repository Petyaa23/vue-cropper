<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;
use Inertia\Inertia;

class ImageController extends Controller
{
    public function index()
    {
        return Inertia::render('ImageCropper');
    }

    public function store(Request $request)
    {
        if ($request->hasFile('file')) {

            $image = $request->file;
            $name = $request->name.'.jpg';
            $path = 'public/images/' . $name;

            $img = Image::make($image);

            Storage::disk('local')->put($path, $img->encode());

            $url = asset('storage/images/' . $name);

            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'No file']);
    }
}
