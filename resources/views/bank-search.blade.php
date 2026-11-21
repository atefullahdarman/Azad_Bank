<x-app-layout title="Search results">
    @if($term === '')
        <section class="panel"><p>Enter a name, email address, account number, or transaction number in the search bar.</p></section>
    @else
        <p class="search-caption">Showing up to five matches per section for <strong>{{ $term }}</strong>.</p>
        <div class="search-results">
            @foreach($groups as $label => $records)
                <section class="panel">
                    <div class="panel-heading"><h2>{{ $label }}</h2></div>
                    <ul class="search-result-list">
                        @forelse($records as [$title, $detail, $url])
                            <li><a href="{{ $url }}"><strong>{{ $title }}</strong><span>{{ $detail }}</span></a></li>
                        @empty
                            <li class="empty-message">No matching records.</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</x-app-layout>
