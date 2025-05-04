<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Gallery;

class GalleryController extends Controller
{
    // INDEX
    public function index()
    {
        // Fetch all media with pagination (12 per page)
        $mediaItems = Media::latest()->paginate(8);
        return view('gallery.index', compact('mediaItems'));
    }
    // INDEX - Using Gallery model binding
        // public function index()
        // {
        //     // Fetch media only assigned to Gallery model, 'default' collection
        //     $mediaItems = Media::where('model_type', Gallery::class)
        //         ->where('collection_name', 'default')
        //         ->latest()
        //         ->paginate(12);
    
        //     return view('gallery.index', compact('mediaItems'));
        // }

    // DELETE
    public function destroy(Media $media)
    {
        // Delete the media item
        $media->delete();

        return redirect()->route('gallery.index')->with('success', 'Image deleted successfully.');
    }
        // DELETE - Using Gallery model binding
        // public function destroy(Gallery $gallery, Media $media)
        // {
        //     // Optional: Check if the media belongs to the Gallery model
        //     if ($media->model_type === Gallery::class) {
        //         $media->delete();
        //         return redirect()->route('gallery.index')->with('success', 'Image deleted successfully.');
        //     }
    
        //     return redirect()->route('gallery.index')->with('error', 'Invalid media item.');
        // }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        // Ensure the gallery exists
        $gallery = Gallery::firstOrCreate(['id' => 1]);
    
        foreach ($request->file('images', []) as $image) {
            $gallery->addMedia($image)->toMediaCollection('default');
        }
    
        return redirect()->route('gallery.index')->with('success', 'Images uploaded successfully.');
    }
    

}
