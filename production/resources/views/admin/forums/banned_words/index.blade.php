@extends('admin.layouts.app')

@section('title', 'Forum Banned Words')

@section('content')
<div class="content">
    @php($admin = auth('admin')->user())
    @php($canModerate = $admin && $admin->hasPermission('forums.moderate'))
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Forum Banned Words</h1>
        <a href="{{ route('admin.forums.index') }}" class="btn btn-alt-secondary">Back to Forums</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Something went wrong:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="block block-rounded mb-3">
        <div class="block-content">
            @if($canModerate)
                <form method="POST" action="{{ route('admin.forums.banned-words.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Add Banned Word</label>
                        <input type="text" name="word" class="form-control" value="{{ old('word') }}" placeholder="e.g. abusiveword" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="block block-rounded">
        <div class="block-content table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th>Status</th>
                        @if($canModerate)
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($words as $word)
                        <tr>
                            <td class="fw-semibold">{{ $word->word }}</td>
                            <td>
                                <span class="badge {{ $word->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $word->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            @if($canModerate)
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.forums.banned-words.toggle', $word) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $word->is_active ? 'btn-alt-warning' : 'btn-alt-success' }}">
                                            {{ $word->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.forums.banned-words.destroy', $word) }}" class="d-inline" onsubmit="return confirm('Delete this word?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-alt-danger">Delete</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canModerate ? 3 : 2 }}" class="text-center text-muted">No banned words yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($words->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $words->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
