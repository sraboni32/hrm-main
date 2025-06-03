<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::with('user')->get(); // Eager load user data
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'required|string|unique:posts,slug',
        ]);

        Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'slug' => $request->slug,
            'user_id' => Auth::id(), // Set the user_id to the currently authenticated user
        ]);

        return redirect()->route('posts.index');
    }

    public function show($slug)
    {
        // Find the post by slug
        $post = Post::where('slug', $slug)->with('user')->firstOrFail();

        // Return the view with the post data
        return view('posts.show', compact('post'));
    }
}
