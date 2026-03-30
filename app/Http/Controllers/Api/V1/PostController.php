<?php

namespace App\Http\Controllers\Api\V1;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use App\Models\Post;

#[Group('Posts', 'Manage blog posts', 20)]
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[Endpoint(title: 'List Posts', description: 'Display a listing of posts.')]
    public function index()
    {
        return PostResource::collection(Post::all());
        }

    /**
     * Store a newly created resource in storage.
     */
    #[Endpoint(title: 'Create Post', description: 'Store a newly created post.')]
    public function store(StorePostRequest $request)
    {
   $data= $request->validated();
   $data['author_id'] = auth()->id(); // Assuming the author_id is the authenticated user's ID
   $post= Post::create($data);
   return response()->json($post,201);
    
    
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
    #[Endpoint(title: 'Show Post', description: 'Display a single post by id.')]
    public function show(Post $post)//id or use route model binding
    {
       // $post = Post::findOrFail($id);
        return 
        response()->json([
            "message" => "Post retrieved successfully",
            "data" => $post
            
            ])->setStatusCode(200);
           
    }

    /**
     * Update the specified resource in storage.
     */
    #[Endpoint(title: 'Update Post', description: 'Update an existing post.')]
    public function update(StorePostRequest $request, Post $post)
    {
        $data = $request->validated();
        $post->update($data);
        return response()->json([
            "message" => "Post updated successfully",
            "data" => $post     // instead of returning the whole post object, you can return only the updated fields if you prefer
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
    #[Endpoint(title: 'Delete Post', description: 'Remove a post from storage.')]
    public function destroy(Post $post)
    {
        $post->delete();
        //return response()->noContent();
    }
}
