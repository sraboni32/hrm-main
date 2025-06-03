@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>All Notifications</span>
                    <form method="POST" action="{{ route('notifications.readAll') }}" id="markAllReadForm">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Mark All as Read</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() === 0)
                        <div class="p-4 text-center text-muted">No notifications found.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $notification->read_at ? '' : 'font-weight-bold bg-light' }}">
                                    <div>
                                        <a href="{{ $notification->data['url'] ?? '#' }}" class="notification-link" data-id="{{ $notification->id }}">
                                            <div>{{ $notification->data['title'] ?? 'Notification' }}</div>
                                            <div class="small text-muted">{{ $notification->data['message'] ?? '' }}</div>
                                        </a>
                                        <div class="small text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="markReadForm">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Mark as Read</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-3 px-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Mark as read via AJAX
$(document).on('submit', '.markReadForm', function(e) {
    e.preventDefault();
    var form = $(this);
    $.post(form.attr('action'), form.serialize(), function() {
        form.closest('li').removeClass('font-weight-bold bg-light');
        form.remove();
    });
});
// Mark all as read via AJAX
$('#markAllReadForm').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function() {
        $('.list-group-item').removeClass('font-weight-bold bg-light');
        $('.markReadForm').remove();
    });
});
</script>
@endsection 