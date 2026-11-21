@props(['title' => 'Dashboard', 'subtitle' => ''])

<x-bank-shell :title="$title" :subtitle="$subtitle">
    @isset($header)
        <div class="legacy-page-header">{{ $header }}</div>
    @endisset
    {{ $slot }}
</x-bank-shell>
