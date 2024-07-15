<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum',except:['index','show'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::withCount('likes')->withCount('comments')->with('comments')->paginate();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required',
            'attachment' => [
                'nullable',
                'file', // Confirm the upload is a file before checking its type.
                function ($attribute, $value, $fail) {
                    $is_image = Validator::make(
                        ['upload' => $value],
                        ['upload' => 'image']
                    )->passes();

                    $is_video = Validator::make(
                        ['upload' => $value],
                        ['upload' => 'mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,video/x-matroska']
                    )->passes();

                    if (!$is_video && !$is_image) {
                        $fail(':attribute must be image or video.');
                    }

                    if ($is_video) {
                        $validator = Validator::make(
                            ['video' => $value],
                            ['video' => "max:102400"]
                        );
                        if ($validator->fails()) {
                            $fail(":attribute must be 10 megabytes or less.");
                        }
                    }

                    if ($is_image) {
                        $validator = Validator::make(
                            ['image' => $value],
                            ['image' => "max:1024"]
                        );
                        if ($validator->fails()) {
                            $fail(":attribute must be one megabyte or less.");
                        }
                    }
                }
            ]
        ]);

        if($request->attachment){
            $uploadPath = $request->file('attachment')->store('postsImage');
        }else{
            $uploadPath = null;
        }

        $post = $request->user()->posts()->create([
            'text' => $request->text,
            'attachment' => $uploadPath
        ]);

        return $post;
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return Post::where('id',$post->id)->withCount('likes')->withCount('comments')->with('comments')->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        Gate::authorize('modify',$post);

        $request->validate([
            'text' => 'required',
            'attachment' => [
                'nullable',
                'file', // Confirm the upload is a file before checking its type.
                function ($attribute, $value, $fail) {
                    $is_image = Validator::make(
                        ['upload' => $value],
                        ['upload' => 'image']
                    )->passes();

                    $is_video = Validator::make(
                        ['upload' => $value],
                        ['upload' => 'mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,video/x-matroska']
                    )->passes();

                    if (!$is_video && !$is_image) {
                        $fail(':attribute must be image or video.');
                    }

                    if ($is_video) {
                        $validator = Validator::make(
                            ['video' => $value],
                            ['video' => "max:102400"]
                        );
                        if ($validator->fails()) {
                            $fail(":attribute must be 10 megabytes or less.");
                        }
                    }

                    if ($is_image) {
                        $validator = Validator::make(
                            ['image' => $value],
                            ['image' => "max:1024"]
                        );
                        if ($validator->fails()) {
                            $fail(":attribute must be one megabyte or less.");
                        }
                    }
                }
            ]
        ]);

        if($request->attachment){
            $post->attachment ? $filePath = $post->attachment : $filePath = null;
            $uploadPath = $request->file('attachment')->store('postsImage');
            $filePath ? Storage::delete($filePath) : null;
        }else{
            $uploadPath = null;
        }

        $post->update([
            'text' => $request->text,
            'attachment' => $uploadPath
        ]);

        return $post;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('modify',$post);

        $post->attachment ? $filePath = $post->attachment : $filePath = null;

        $post->delete();

        $filePath ? Storage::delete($filePath) : null;

        return ['message' => 'This post was deleted!'];
    }

    public function userLikedPosts()
    {
        $user_id = auth('sanctum')->user()->id;
        return Post::whereRelation('likes', 'user_id', $user_id)->withCount('likes')->withCount('comments')->with('comments')->paginate();
    }
}
