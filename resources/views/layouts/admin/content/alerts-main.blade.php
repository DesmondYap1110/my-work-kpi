@if (session('status'))
    <div class="alert alert-success" role="alert">
        <p class="alert-heading">{{ session('status') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        @foreach ($errors->all() as $error)
            <p class="alert-heading mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif
