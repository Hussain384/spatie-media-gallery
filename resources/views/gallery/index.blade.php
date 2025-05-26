@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gallery') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Uploaded Media</h3>
                    <!-- Upload Images & Videos Button -->
                    <form id="media-upload-form" action="{{ route('gallery.media.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex items-center gap-4">
                        @csrf
                        <input type="file" id="media-files-input" name="media_files[]" multiple accept="image/*,video/*" required class="block w-full text-sm text-gray-500">
                        <button type="submit" id="upload-btn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center gap-2">
                            <span id="upload-btn-text">Upload</span>
                            <svg id="upload-spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Preview Selected Files -->
                    <div id="media-preview" class="flex flex-wrap gap-2 mt-2"></div>

                    @if ($errors->any())
                        <div class="bg-red-500 text-white p-2 rounded-lg mt-2">
                            <strong>Upload failed:</strong>
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-500 text-white p-2 rounded-lg mt-2">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                        @foreach ($mediaItems as $media)
                            <div class="relative border p-2 rounded-lg overflow-hidden">
                                @if(Str::startsWith($media->mime_type, 'video/'))
                                    <video controls class="w-full h-48 object-cover rounded-lg">
                                        <source src="{{ $media->getUrl() }}" type="{{ $media->mime_type }}">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <img src="{{ $media->getUrl() }}" 
                                         alt="Uploaded Image" 
                                         class="w-full h-48 object-cover rounded-lg">
                                @endif

                                <!-- Show Collection Name -->
                                <p class="mt-2 text-sm text-gray-600 text-center">
                                    Collection: <span class="font-medium">{{ $media->collection_name }}</span>
                                </p>
                                
                                <!-- Delete Button -->
                                <form action="{{ route('gallery.media.destroy', $media->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this image?');"
                                      class="absolute top-2 right-2 z-10">
                                    
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-full text-xs hover:bg-red-600 transition">
                                        ✖
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $mediaItems->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Interactivity Scripts -->
    <script>
        // Show spinner and disable button on upload
        document.getElementById('media-upload-form').addEventListener('submit', function() {
            document.getElementById('upload-btn').disabled = true;
            document.getElementById('upload-btn-text').textContent = 'Uploading...';
            document.getElementById('upload-spinner').classList.remove('hidden');
        });

        // Preview selected files
        document.getElementById('media-files-input').addEventListener('change', function(event) {
            const preview = document.getElementById('media-preview');
            preview.innerHTML = '';
            Array.from(event.target.files).forEach(file => {
                let el;
                if (file.type.startsWith('image/')) {
                    el = document.createElement('img');
                    el.className = 'h-16 w-16 object-cover rounded border';
                    el.src = URL.createObjectURL(file);
                } else if (file.type.startsWith('video/')) {
                    el = document.createElement('video');
                    el.className = 'h-16 w-16 object-cover rounded border';
                    el.src = URL.createObjectURL(file);
                    el.controls = true;
                } else {
                    el = document.createElement('span');
                    el.textContent = file.name;
                }
                preview.appendChild(el);
            });
        });
    </script>
</x-app-layout>
