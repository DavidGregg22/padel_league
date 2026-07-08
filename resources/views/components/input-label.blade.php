@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-blue-200']) }}>
    {{ $value ?? $slot }}
</label>
