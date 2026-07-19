@extends('admin.layouts.app')

@section('page_title', 'Edit: ' . $product->name)
@section('page_subtitle', 'Update product details')

@section('page_actions')
    <a href="{{ route('admin.products.index') }}"
       class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $product->name) }}"
                    required
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm
                           @error('name') border-red-400 @enderror"
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm resize-none
                           @error('description') border-red-400 @enderror"
                >{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Price + Stock --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="price"
                        type="number"
                        name="price"
                        value="{{ old('price', $product->price) }}"
                        step="0.01"
                        min="0"
                        required
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm
                               @error('price') border-red-400 @enderror"
                    >
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input
                        id="stock"
                        type="number"
                        name="stock"
                        value="{{ old('stock', $product->stock) }}"
                        min="0"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm
                               @error('stock') border-red-400 @enderror"
                    >
                    @error('stock')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Sizes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Available Sizes</label>
                {{-- Hidden field ensures sizes are cleared when all checkboxes are unchecked --}}
                <input type="hidden" name="sizes" value="">
                <div class="flex flex-wrap gap-3">
                    @foreach(\App\Models\Product::SIZES as $size)
                        <label class="inline-flex items-center gap-2 border border-gray-300 rounded-xl px-4 py-2.5 cursor-pointer hover:border-gray-400 transition">
                            <input
                                type="checkbox"
                                name="sizes[]"
                                value="{{ $size }}"
                                @checked(in_array($size, old('sizes', $product->sizes ?? [])))
                                class="w-4 h-4 rounded border-gray-300 accent-black cursor-pointer"
                            >
                            <span class="text-sm">{{ $size }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-1">Leave empty if the product has no size options</p>
                @error('sizes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('sizes.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select
                    id="category_id"
                    name="category_id"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm bg-white
                           @error('category_id') border-red-400 @enderror"
                >
                    <option value="">No Category</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(old('category_id', $product->category_id) == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active toggle --}}
            <div class="flex items-start gap-3">
                <input type="hidden" name="active" value="0">
                <div class="flex items-center h-5 mt-0.5">
                    <input
                        id="active"
                        type="checkbox"
                        name="active"
                        value="1"
                        @checked(old('active', $product->active))
                        class="w-4 h-4 rounded border-gray-300 accent-black cursor-pointer"
                    >
                </div>
                <div>
                    <label for="active" class="block text-sm font-medium text-gray-700 cursor-pointer">Active</label>
                    <p class="text-xs text-gray-400 mt-0.5">Visible to customers on the storefront</p>
                </div>
            </div>

            {{-- Add more images --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Add More Images</label>
                <div
                    class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-gray-400 transition cursor-pointer"
                    onclick="document.getElementById('images').click()"
                >
                    <svg class="mx-auto w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Click to upload additional images</p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, WebP — multiple allowed</p>
                    <input
                        id="images"
                        type="file"
                        name="images[]"
                        multiple
                        accept="image/*"
                        class="hidden"
                        onchange="previewImages(this)"
                    >
                </div>
                <div id="image-preview" class="flex gap-3 mt-3 flex-wrap"></div>
                @error('images')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-100">

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    class="bg-black text-white px-6 py-2.5 rounded-lg hover:bg-gray-800 transition text-sm font-medium"
                >
                    Save Changes
                </button>
                <a href="{{ route('admin.products.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    {{-- ── Current Images ── --}}
    @if($product->images->isNotEmpty())
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6 lg:p-8" id="images" style="margin-left: 0;">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Current Images</h2>
            <p class="text-xs text-gray-400 mb-4">{{ $product->images->count() }} {{ $product->images->count() === 1 ? 'image' : 'images' }} uploaded</p>

            <div class="flex flex-wrap gap-4">
                @foreach($product->images as $image)
                    <div class="relative flex-shrink-0">
                        <img
                            src="{{ asset('storage/' . $image->path) }}"
                            alt="{{ $image->alt ?? $product->name }}"
                            class="w-20 h-20 rounded-xl object-cover border border-gray-200 bg-gray-50"
                        >
                        @if($image->is_main)
                            <span class="absolute -top-2 -right-2 bg-black text-white text-[10px] px-1.5 py-0.5 rounded-full leading-none font-medium whitespace-nowrap">
                                Main
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

<script>
var MAX_FILE_BYTES  = 2 * 1024 * 1024;  // 2 MB — sinkron dengan validasi `images.*|max:2048`
var MAX_TOTAL_BYTES = 7 * 1024 * 1024;  // aman di bawah post_max_size PHP (8 MB)

function previewImages(input) {
    var preview = document.getElementById('image-preview');
    preview.innerHTML = '';

    var files = Array.from(input.files);
    var oversized = files.filter(function (f) { return f.size > MAX_FILE_BYTES; });
    var totalSize = files.reduce(function (sum, f) { return sum + f.size; }, 0);

    if (oversized.length > 0 || totalSize > MAX_TOTAL_BYTES) {
        var msg = oversized.length > 0
            ? 'Gambar berikut melebihi 2 MB:\n- ' + oversized.map(function (f) {
                  return f.name + ' (' + (f.size / 1024 / 1024).toFixed(1) + ' MB)';
              }).join('\n- ')
            : 'Total ukuran gambar melebihi 7 MB. Unggah lebih sedikit gambar sekaligus.';
        alert(msg);
        input.value = '';
        return;
    }

    files.forEach(function (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var div = document.createElement('div');
            div.className = 'w-20 h-20 rounded-xl overflow-hidden border border-gray-200 bg-gray-50 flex-shrink-0';
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-full object-cover';
            div.appendChild(img);
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endsection
