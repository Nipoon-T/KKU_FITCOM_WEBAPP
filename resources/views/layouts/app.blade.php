<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @yield('content')
    </flux:main>
</x-layouts::app.sidebar>