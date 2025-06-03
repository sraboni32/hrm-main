<!-- resources/views/posts/show.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>
    <p>Posted by {{ $post->user->name }} on {{ $post->created_at }}</p>
    <a href="{{ route('posts.index') }}">Back to Posts</a>
@endsection