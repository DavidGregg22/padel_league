@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-blue-800 border-blue-700 text-white placeholder-blue-500 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm w-full']) }}>
