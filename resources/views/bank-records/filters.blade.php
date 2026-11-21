<div class="records-toolbar">
    <span class="records-toolbar-copy">{{ number_format($records->total()) }} {{ $section }}</span>
    <a class="button button-primary" href="{{ route($section.'.create') }}"><x-icon name="plus" size="17" /> {{ $createLabel }}</a>
</div>
<form method="GET" action="{{ route($section.'.index') }}" class="records-toolbar records-toolbar-actions" aria-label="Filter {{ $section }}">
    <label class="search-field">
        <x-icon name="search" />
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="150" placeholder="{{ $searchPlaceholder }}" aria-label="Search {{ $section }}">
    </label>
    @if(!empty($types))
        <label class="field">
            <select name="type" aria-label="Account type">
                <option value="">All types</option>
                @foreach($types as $value => $text)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </label>
    @endif
    <label class="field">
        <select name="status" aria-label="Status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </label>
    <label class="field">
        <select name="sort" aria-label="Sort by">
            @foreach($sorts as $value => $text)
                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $text }}</option>
            @endforeach
        </select>
    </label>
    <button class="button button-secondary" type="submit"><x-icon name="filter" size="16" /> Apply</button>
    @if(array_filter($filters))
        <a class="button button-quiet" href="{{ route($section.'.index') }}">Clear</a>
    @endif
</form>
