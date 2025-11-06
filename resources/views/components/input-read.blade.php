@props(['label', 'value' => ''])
<div>
    <label class="block text-xs text-gray-600 mb-1">{{ $label }}</label>
    <input type="text" value="{{ $value }}" readonly
        class="w-full border border-gray-300 rounded-md bg-gray-100 text-gray-800 px-2 py-1 text-sm">
</div>
