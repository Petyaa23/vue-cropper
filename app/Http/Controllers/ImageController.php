<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::all();
        return Inertia::render('ImageCropper')->with(compact("images"));
    }
    public function store(Request $request)
    {
        $name = uniqid() . '.png';
        $path = '/storage/upload/' . $name;
        $request->file('image')->move(public_path('/storage/upload'), $name);
        $request = Image::create([
            'title' => $path
        ]);

        return response()->json(['success'=>'Crop Image Saved/Uploaded Successfully using jQuery and Ajax In Laravel']);
    }



}
