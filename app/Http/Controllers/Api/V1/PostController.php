<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user=request()->user();
        $posts=$user->posts()->get();
        return PostResource::collection($posts);
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
   $data= $request->validated();
   $data['author_id'] = $request->user()->id; // Assuming the author_id is the authenticated user's ID
   $post= Post::create($data);
   return new PostResource($post);
    
    
    
    // return response()->json([  
    //         "id" => 1,
    //             "title" => "Post Title",
    //             "content" => "Post Content"
    //         ],200);
    //     // ->setStatusCode(200);
           
    } 

    /**
     * Display the specified resource.
     */
    public function show(Post $post)//id or use route model binding
    {
       // $post = Post::findOrFail($id);
       $user = request()->user(); 
       //if ($user->id !== $post->author_id) {
        abort_if(Auth::id() != $post->author_id, 403, 'Unauthorized access to this post');
     //  
        return 
        response()->json([
            "message" => "Post retrieved successfully",
            "data" => new PostResource($post)
            
            ])->setStatusCode(200);
           
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, Post $post)
    {
     abort_if(Auth::id() != $post->author_id, 403, 'Unauthorized access to this post');

        $data = $request->validated();
        $post->update($data);
        return response()->json([
            "message" => "Post updated successfully",
            "data" => new PostResource($post)     // instead of returning the whole post object, you can return only the updated fields if you prefer
            // [
            //     "id" => $post->id,
            //     "title" => $data['title'],
            //     "content" => $data['content']
            // ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        abort_if(Auth::id() != $post->author_id, 403, 'Unauthorized access to this post');
        $post->delete();
        //return response()->noContent();
    }
}
