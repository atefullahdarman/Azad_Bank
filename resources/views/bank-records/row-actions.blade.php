<div class="table-actions">
    <a class="table-action" href="{{ route($section.'.show', $record) }}" aria-label="View {{ $label }}" title="View {{ $label }}"><x-icon name="eye" size="15" /></a>
    @if($canEdit ?? true)
        <a class="table-action" href="{{ route($section.'.edit', $record) }}" aria-label="Edit {{ $label }}" title="Edit {{ $label }}"><x-icon name="edit" size="15" /></a>
    @endif
    @if($canDelete ?? true)
        <form method="POST" action="{{ route($section.'.destroy', $record) }}" data-confirm="Delete this {{ $label }}?">
            @csrf
            @method('DELETE')
            <button class="table-action table-action-danger" type="submit" aria-label="Delete {{ $label }}" title="Delete {{ $label }}"><x-icon name="trash" size="15" /></button>
        </form>
    @endif
</div>
