@php
    $shellTitle = trim($__env->yieldContent('title', 'Dashboard'));
    $shellSubtitle = trim($__env->yieldContent('subtitle', ''));
@endphp
<x-bank-shell :title="$shellTitle" :subtitle="$shellSubtitle">
    @yield('content')
</x-bank-shell>
