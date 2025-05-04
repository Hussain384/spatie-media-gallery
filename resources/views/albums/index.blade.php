<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Albums') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Create New Album</h3>
                    
                    <form action="{{ route('albums.store') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="text" name="title" placeholder="Album Name" required class="block w-full text-sm text-gray-500 border rounded-lg cursor-pointer dark:text-gray-400">
                        <button type="submit" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Create Album</button>
                    </form>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold">Your Albums</h3>
                        @foreach ($albums as $album)
                            <div class="border p-4 rounded-lg mt-4">
                                <div class="flex justify-between items-center  px-4">
                                    <h1 class="text-2xl font-extrabold ">{{ $album->title }}</h1>
                                    <!-- Add from Gallery -->
                                    <button type="button" onclick="openGalleryModal({{ $album->id }})" 
                                        class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600">
                                        Add from Gallery
                                    </button>
                                    <!-- Edit Album -->
                                    <a href="{{ route('albums.edit', $album) }}" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">
                                        Edit Album
                                    </a>
                                </div>

                                <!-- Upload Images -->
                                <form action="{{ route('albums.upload', $album) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                                    @csrf
                                    <div class="flex justify-between items-center  px-4">
                                        <input type="file" name="media[]" multiple class="block w-11/12 text-sm text-gray-500 border rounded-lg cursor-pointer dark:text-gray-400">
                                        <button type="submit" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Upload</button>
                                    </div>
                                </form>



                                <!-- Display Album Images -->
                                <div class="flex space-x-4 overflow-x-auto p-2">
                                    @foreach ($album->getMedia(Str::slug($album->title, '_')) as $media)
                                        <div class="relative border p-2 rounded-lg overflow-hidden flex-shrink-0">
                                            <img src="{{ $media->getUrl() }}" alt="Album Image" class="w-48 h-48 object-cover rounded-lg">
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('albums.media.destroy', ['album' => $album->id, 'media' => $media->id]) }}" 
                                                method="POST" 
                                                onsubmit="return confirm('Are you sure you want to delete this image?');"
                                                class="absolute top-1 right-1 z-50 bg-white bg-opacity-75 p-1 rounded-full shadow-md">
                                                
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="bg-red-500 text-white relative px-2 py-1 rounded-full text-xs hover:bg-red-600 transition">
                                                    ✖
                                                </button>
                                            </form>

                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>

<!-- Modal -->
<div id="galleryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-3/4 h-3/4 overflow-auto">
        <h2 class="text-lg font-semibold mb-4">Select Images from Gallery</h2>

        <div id="galleryGrid" class="grid grid-cols-4 gap-4">
            @foreach ($galleryMedia as $media)
                <div 
                    onclick="toggleMediaSelection(this, {{ $media->id }})" 
                    data-media-id="{{ $media->id }}"
                    class="border rounded cursor-pointer p-1 hover:border-blue-500 relative transition-all duration-200"
                >
                    <img src="{{ $media->getUrl() }}" class="w-full h-32 object-cover rounded">
                    
                    <!-- Overlay shown when selected -->
                    <div class="selected-overlay absolute inset-0 bg-blue-500 bg-opacity-50 hidden rounded flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end">
            <button onclick="attachImages()" class="bg-green-500 text-white px-4 py-2 rounded">Attach Selected</button>
            <button onclick="closeGalleryModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
        </div>
    </div>
</div>

<script>
    let selectedMedia = [];

function toggleMediaSelection(element, mediaId) {
    if (selectedMedia.includes(mediaId)) {
        // Deselect
        selectedMedia = selectedMedia.filter(id => id !== mediaId);
        element.querySelector('.selected-overlay').classList.add('hidden');
        element.classList.remove('border-blue-500');
    } else {
        // Select
        selectedMedia.push(mediaId);
        element.querySelector('.selected-overlay').classList.remove('hidden');
        element.classList.add('border-blue-500');
    }
}

function attachImages() {
    const albumId = document.getElementById('galleryModal').getAttribute('data-album-id');
    fetch(`/albums/${albumId}/attach-gallery`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ media_ids: selectedMedia })
    }).then(() => {
        window.location.reload(); // Refresh to show updated album
    });
}

function openGalleryModal(albumId) {
    document.getElementById('galleryModal').classList.remove('hidden');
    document.getElementById('galleryModal').setAttribute('data-album-id', albumId);
    selectedMedia = []; // Reset selection when modal opens
}

function closeGalleryModal() {
    document.getElementById('galleryModal').classList.add('hidden');
}

</script>

</x-app-layout>


