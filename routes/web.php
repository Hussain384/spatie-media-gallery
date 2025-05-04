<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GalleryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware(['auth'])->group(function () {
    // Albums
    Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::post('/albums/{album}/upload', [AlbumController::class, 'upload'])->name('albums.upload');
    Route::get('/albums/{album}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::put('/albums/{album}', [AlbumController::class, 'update'])->name('albums.update');
    Route::delete('/albums/{album}/media/{media}', [AlbumController::class, 'destroyMedia'])->name('albums.media.destroy');


    // temp storage for dropzone
    Route::post('/albums/upload-temp', function (Request $request) {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    
        $file = $request->file('file');
    
        if (!$file->isValid()) {
            return response()->json(['error' => 'File upload failed'], 400);
        }
    
        $filename = time() . '_' . $file->getClientOriginalName();
        Storage::disk('local')->put("temp/" . $filename, file_get_contents($file));
    
        return response()->json(['file_name' => $filename]);
    })->name('albums.upload.temp');
    

    // Gallery Images 
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::delete('/gallery/media/{media}', [GalleryController::class, 'destroy'])->name('gallery.media.destroy');
    Route::post('/gallery/media', [GalleryController::class, 'store'])->name('gallery.media.store');


    // Attach Images
    Route::post('/albums/{album}/attach-gallery', [AlbumController::class, 'attachGallery'])->name('albums.attach.gallery');


        
});
