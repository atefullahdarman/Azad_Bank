@if($errors->any())
    <div class="form-errors" role="alert">
        <strong>Review the following fields.</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
