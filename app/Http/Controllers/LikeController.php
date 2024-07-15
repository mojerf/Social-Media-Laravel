<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class LikeController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum',except:['index','show'])
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'post_id'=>'required|exists:posts,id',
        ]);

        $like = $request->user()->likes()->firstOrCreate($fields);

        return $like;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Like $like)
    {
        Gate::authorize('modify',$like);

        $like->delete();

        return ['message' => 'The like is removed!'];
    }
}
