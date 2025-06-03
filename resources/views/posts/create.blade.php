<!-- resources/views/posts/create.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Create Post</h1>
    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <div>
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>
        </div>
        <div>
            <label for="content">Content</label>
            <textarea name="content" id="content" required></textarea>
        </div>
        <div>
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" required>
        </div>
        <button type="submit">Create Post</button>
    </form>
@endsection