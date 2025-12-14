@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border-gray-300 focus:border-mint-dark focus:ring-mint-dark text-sm shadow-sm transition',
    ]) }}
>
