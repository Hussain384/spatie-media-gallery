<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Album: {{ $album->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-black">

                    <!-- Edit Album Name -->
                    <form id="albumForm" action="{{ route('albums.update', $album) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Album Name</label>
                        <p class="block w-full mt-1 border border-gray-300 rounded-lg shadow-sm p-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $album->title }}
                        </p>

                        <!-- Dropzone Area -->
                        <h3 class="mt-6 text-lg text-white font-semibold">Album Images</h3>
                        <div id="dropzone" class="dropzone border-dashed border-2 border-gray-300 p-4 rounded-lg"></div>

                        <!-- Hidden Input for Removed Files -->
                        <input type="hidden" name="removed_files" id="removed_files">

                        <!-- Save Button -->
                        <button type="submit" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                            Save Changes
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Pass existing images to JavaScript -->
    @php
        $existingImages = $album->getMedia(Str::slug($album->title, '_'))
        ->map(function ($media) {
            return [
                "id" => $media->id,
                "name" => $media->file_name, // Use "name" instead of "file_name"
                "url" => $media->getUrl(), // Full URL of the image
            ];
        });
    @endphp
    <input type="hidden" id="existingImagesData" value='@json($existingImages)'>

 <script>
        Dropzone.autoDiscover = false;

// Retrieve existing images from the hidden input
const existingImagesData = document.getElementById("existingImagesData").value;
const existingImages = JSON.parse(existingImagesData);

// Array to track removed files
let removedFiles = [];

let myDropzone = new Dropzone("#dropzone", {
    url: "{{ route('albums.update', $album) }}",
    paramName: "images",
    maxFilesize: 2, // MB
    acceptedFiles: "image/*",
    maxFiles: 3,
    headers: {
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    autoProcessQueue: false, // Disable auto upload
    parallelUploads: 10,
    addRemoveLinks: true,

    init: function () {
        let dropzoneInstance = this;

        // Add existing images to Dropzone
        existingImages.forEach(image => {
            let mockFile = { name: image.name, size: 12345 }; // Fake file object
            dropzoneInstance.emit("addedfile", mockFile);
            dropzoneInstance.emit("thumbnail", mockFile, image.url); // Set preview image
            dropzoneInstance.emit("complete", mockFile);
            mockFile.mediaId = image.id;
        });

        // Handle form submission
        document.getElementById("albumForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent default form submission
            
            // Disable submit button to prevent multiple clicks
            let submitButton = this.querySelector("button[type='submit']");
            submitButton.disabled = true;
            submitButton.innerText = "Saving...";

            // Track removed files
            document.getElementById("removed_files").value = JSON.stringify(removedFiles);

            if (dropzoneInstance.getQueuedFiles().length > 0) {
                // Process Dropzone queue first
                dropzoneInstance.processQueue();
            } else {
                // No new files, submit form manually
                submitForm();
            }
        });

        // When Dropzone finishes processing all files, submit form
        this.on("queuecomplete", function () {
            submitForm();
        });

        // Handle file removal
        this.on("removedfile", function (file) {
            if (file.mediaId) {
                removedFiles.push(file.mediaId);
                console.log("Removed file with ID:", file.mediaId);
            }
        });

        function submitForm() {
            let form = document.getElementById("albumForm");
            let formData = new FormData(form);

            dropzoneInstance.getAcceptedFiles().forEach((file) => {
                if (!file.mediaId) { // Only append new files
                    formData.append("images[]", file);
                }
            });

            fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ route('albums.index') }}";
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
    }
});

</script> 
 
 
 
 
 <!-- issue require double clicks -->
 <!-- <script>
        Dropzone.autoDiscover = false;

        // Retrieve existing images from the hidden input
        const existingImagesData = document.getElementById("existingImagesData").value;
        const existingImages = JSON.parse(existingImagesData);

        // Array to track removed files
        let removedFiles = [];

        let myDropzone = new Dropzone("#dropzone", {
            url: "{{ route('albums.update', $album) }}", // This will be overridden by form submission
            paramName: "images", // Name of the file input
            maxFilesize: 2, // MB
            acceptedFiles: "image/*",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            autoProcessQueue: false, // Disable auto upload
            parallelUploads: 10,
            addRemoveLinks: true,

            init: function () {
                let dropzoneInstance = this;

                // Add existing images to Dropzone
                existingImages.forEach(image => {
                    let mockFile = { name: image.name, size: 12345 }; // Mock file object
                    dropzoneInstance.emit("addedfile", mockFile);
                    dropzoneInstance.emit("thumbnail", mockFile, image.url); // Set thumbnail
                    dropzoneInstance.emit("complete", mockFile);

                    // Add a custom attribute to track the media ID
                    mockFile.mediaId = image.id;
                });

                // Handle form submission
                document.getElementById("albumForm").addEventListener("submit", function (event) {
                    event.preventDefault(); // Prevent default form submission

                    // Append Dropzone files to the form data
                    let formData = new FormData(this);
                    dropzoneInstance.getAcceptedFiles().forEach((file) => {
                        if (!file.mediaId) { // Only append new files (not existing ones)
                            formData.append("images[]", file);
                        }
                    });

                    // Track removed files
                    let removedFiles = [];
                    dropzoneInstance.getFilesWithStatus(Dropzone.REMOVED_FILE).forEach((file) => {
                        if (file.mediaId) {
                            removedFiles.push(file.mediaId); // Track removed media IDs
                        }
                    });
                    document.getElementById("removed_files").value = JSON.stringify(removedFiles);

                    // Submit the form with files
                    fetch(this.action, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "{{ route('albums.index') }}"; // Redirect on success
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
                });

                // Handle file removal
                this.on("removedfile", function (file) {
                    if (file.mediaId) {
                        console.log("Removed existing file with ID:", file.mediaId);
                    }
                });
            }
        });
    </script> -->

</x-app-layout>