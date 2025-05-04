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
                    <h3 class="text-lg font-semibold">Uploaded Images</h3>
                    <!-- Upload Images Button -->
                     
                    <form action="{{ route('gallery.media.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex items-center gap-4">
                        @csrf
                            <input type="file" name="images[]" multiple accept="image/*" required class="block w-full text-sm text-gray-500">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                Upload
                            </button>
                        
                    </form>


                    @if (session('success'))
                        <div class="bg-green-500 text-white p-2 rounded-lg mt-2">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                        @foreach ($mediaItems as $media)
                            <div class="relative border p-2 rounded-lg overflow-hidden">
                                <img src="{{ $media->getUrl() }}" 
                                     alt="Uploaded Image" 
                                     class="w-full h-48 object-cover rounded-lg">

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
</x-app-layout>
