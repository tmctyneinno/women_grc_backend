@extends('admin.layouts.app')

@section('title', 'Articles')

@section('content')
@php
    $adminUser = auth('admin')->user();
    $isSuperAdmin = $adminUser && method_exists($adminUser, 'isSuperAdmin')
        ? $adminUser->isSuperAdmin()
        : ($adminUser && strtolower($adminUser->email) === 'enquiries@wgrcfp.org');
@endphp
<div class="content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h1 class="h3 mb-1">Articles</h1>
            <p class="text-muted mb-0">Manage event articles submitted by admins and users.</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary mt-3 mt-md-0">
            <i class="fa fa-plus me-1"></i> Create Article
        </a>
    </div>

    <div class="block block-rounded">
        <div class="block-content block-content-full">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (['draft','pending','published','rejected'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Search articles..." value="{{ $q }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Author</th>
                            <th>Tag</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $article->title }}</div>
                                    <div class="text-muted fs-sm">{{ \Illuminate\Support\Str::limit($article->summary, 80) }}</div>
                                </td>
                                <td>
                                    <span class="badge 
                                        {{ $article->status === 'published' ? 'bg-success' : 
                                           ($article->status === 'pending' ? 'bg-warning' : 
                                           ($article->status === 'rejected' ? 'bg-danger' : 'bg-secondary')) }}">
                                        {{ ucfirst($article->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($article->creatorUser)
                                        {{ $article->creatorUser->first_name }} {{ $article->creatorUser->last_name }}
                                        <div class="text-muted fs-sm">User</div>
                                    @elseif($article->creatorAdmin)
                                        {{ $article->creatorAdmin->name }}
                                        <div class="text-muted fs-sm">Admin</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $article->tag ?? '-' }}</td>
                                <td>{{ $article->created_at?->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-alt-primary">Edit</a>
                                        @if($isSuperAdmin && $article->status === 'pending')
                                            <form method="POST" action="{{ route('admin.articles.approve', $article) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.articles.reject', $article) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-danger" type="submit">Reject</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-alt-secondary" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No articles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
