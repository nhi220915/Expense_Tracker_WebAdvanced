<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-mint-dark to-emerald text-white text-xs font-semibold uppercase tracking-wide shadow hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mint-dark transition',
]) }}>
    {{ $slot }}
</button>
