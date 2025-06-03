<!-- resources/views/posts/index.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Posts</h1>
    <a href="{{ route('posts.create') }}">Create New Post</a>
    <ul>
        @foreach ($posts as $post)
            <li>
                <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a> by {{ $post->user->name }}
            </li>
        @endforeach
    </ul>
@endsection