<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class
PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('index', 'show');
    }

    public function index()
    {
//        $posts = Post::all();
//        $post = Post::where('title', 'Post Two')->get();
//        $posts = DB::select('SELECT * FROM posts');
//        $posts = Post::orderBy('title', 'desc')->take(1)->get();
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body'  => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);
//        Handle File Upload
        if($request->hasFile('cover_image')){
//            Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
//            Get jst filename
            $fielname = pathinfo($filenameWithExt, PATHINFO_FILENAME);
//            Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();
//            Filename to store
            $fileNameToStore = $fielname.'_'.time().'.'.$extension;
//            Upload Image
            $path = $request->file('cover_image')->storeAs('public/images', $fileNameToStore);
        } else {
            $fileNameToStore = 'noimage.jpg';
        }
//        Create post
        $post = new Post();
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id;
        $post->cover_image = $fileNameToStore;
        $post->save();

        return redirect('/posts')->with('success', 'Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
//        Check for correct user
        if(auth()->user()->id !== $post->user_id){
            return redirect('/posts')->with('error', 'Unauthorized Page');
        }
        return view('posts.edit')->with('post', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'body'  => 'required',
        ]);

        //Handle File Upload
        if($request->hasFile('cover_image')){
//            Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
//            Get jst filename
            $fielname = pathinfo($filenameWithExt, PATHINFO_FILENAME);
//            Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();
//            Filename to store
            $fileNameToStore = $fielname.'_'.time().'.'.$extension;
//            Upload Image
            $path = $request->file('cover_image')->storeAs('public/images', $fileNameToStore);
        }

//        Find post
        $post = Post::find($id);
        $post->title = $request->input('title');
        if($request->hasFile('cover_image')){
            $post->cover_image = $fileNameToStore;
        }
        $post->body = $request->input('body');
        $post->save();

        return redirect("/posts/$id")->with('success', 'Post Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if(auth()->user()->id !== $post->user_id){
            return redirect('/posts')->with('error', 'Unauthorized Page');
        }
        if($post->cover_image != 'noimage.jpg'){
//            Delete image
            Storage::delete('public/images/'.$post->cover_image);
        }
        $post->delete();
        return redirect('/posts')->with('success', 'Post removed');
    }
}
