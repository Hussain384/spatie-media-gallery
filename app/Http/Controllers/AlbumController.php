<?php

namespace App\Http\Controllers;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use App\Models\Gallery;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::with('media')->where('user_id', Auth::id())->latest()->get();
        $galleryMedia = Media::where('collection_name', 'default')->get(); // Adjust collection_name as per your usage
        return view('albums.index', compact('albums','galleryMedia'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        Album::create([
            'title' => $request->title,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Album created successfully!');
    }

    public function upload(Request $request, Album $album)
    {
        $request->validate([
            'media.*' => 'required|image|max:2048', // Multiple images
        ]);

        $collectionName = Str::slug($album->title, '_'); // Convert title to safe collection name

        foreach ($request->file('media') as $image) {
            $album->addMedia($image)->toMediaCollection($collectionName);
        }

        return back()->with('success', 'Images uploaded successfully!');
    }

    public function edit(Album $album)
    {
        return view('albums.edit', compact('album'));
    }

    // UPDATE ALBUM
    public function update(Request $request, Album $album)
    {
        $request->validate([
            // 'title' => 'required|string|max:255',
            'images.*' => 'image|max:2048', // Validate each image
        ]);

        // $album->update(['title' => $request->title]);
        $collectionName = Str::slug($album->title, '_'); // Convert title to safe collection name

        // Handle new uploaded files
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $album->addMedia($image)->toMediaCollection($collectionName);
            }
        }

        // Handle removed files
        if ($request->has('removed_files')) {
            $removedFiles = json_decode($request->removed_files, true);
            foreach ($removedFiles as $mediaId) {
                $album->media()->find($mediaId)?->delete();
            }
        }

        return response()->json(['success' => true]);
    }
    public function destroyMedia(Album $album, Media $media)
    {
        // Ensure the media belongs to the correct album
        if ($media->model_id !== $album->id) {
            return back()->with('error', 'Unauthorized action.');
        }
    
        $media->delete(); // Remove from database and storage
    
        return back()->with('success', 'Image deleted successfully.');
    }


    // Attach Images

    public function attachGallery(Request $request, Album $album)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
        ]);
    
        // Fetch the selected gallery images that belong to the Gallery model
        $galleryMedia = Media::whereIn('id', $request->media_ids)
                            ->where('model_type', Gallery::class)
                            ->get();
    
        foreach ($galleryMedia as $media) {
            // Reassign the media to the Album model
            $media->model_type = Album::class;
            $media->model_id = $album->id;
            $media->collection_name = Str::slug($album->title, '_'); // Optional: set to album-specific collection
            $media->save();
        }
    
        return back()->with('success', 'Images moved from gallery to album.');
    }
    

}
