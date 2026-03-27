@extends('admin.layouts.app')

@section('title', 'Email Event Bookers')

@section('content')
<div class="content">
    <div class="block block-rounded overflow-hidden">
        <div class="block-content block-content-full bg-primary-light">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Email Bookers</h2>
                    <p class="text-muted mb-0">Send a message to everyone who booked <strong>{{ $event->title }}</strong>.</p>
                </div>
                <div class="text-md-end">
                    <div class="fs-sm text-muted">Total bookers</div>
                    <div class="fs-3 fw-bold">{{ $bookingsCount }}</div>
                </div>
            </div>
        </div>

        <div class="block-content">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h4 class="block-title">Compose Email</h4>
                            <a href="{{ route('admin.events.bookings', $event) }}" class="btn btn-sm btn-alt-secondary">
                                &larr; Back to Bookings
                            </a>
                        </div>
                        <div class="block-content">
                            <form action="{{ route('admin.events.bookings.email', $event) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="subject">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required>{{ old('message') }}</textarea>
                                    <div class="form-text">This will appear as the main body of the email.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="button_text">Button Text (optional)</label>
                                        <input type="text" class="form-control" id="button_text" name="button_text" value="{{ old('button_text', 'View details') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="button_url">Button Link (optional)</label>
                                        <input type="url" class="form-control" id="button_url" name="button_url" value="{{ old('button_url') }}" placeholder="https://...">
                                    </div>
                                </div>

                                <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-paper-plane me-1"></i> Send Email
                                    </button>
                                    <a href="{{ route('admin.events.bookings', $event) }}" class="btn btn-alt-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="block block-rounded">
                        <div class="block-header">
                            <h4 class="block-title">Preview Notes</h4>
                        </div>
                        <div class="block-content">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success">New</span>
                                </div>
                                <div>
                                    <p class="mb-2">Your email uses the branded WGRCFP template with the event title at the top.</p>
                                    <p class="mb-0">If you add a link, it will appear as a button in the email.</p>
                                </div>
                            </div>
                            <hr>
                            <div class="bg-body p-3 rounded">
                                <div class="fw-bold mb-1">Event</div>
                                <div class="text-muted">{{ $event->title }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
