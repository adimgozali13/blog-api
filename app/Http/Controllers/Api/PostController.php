<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $paginate = $request->paginate ?? 10;

            $post = Post::paginate($paginate);
            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => "Success !"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Internal Server Error !"
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $post = Post::create([
                'title' => $request->title,
                'body' => $request->body,
                'user_id' => Auth::user()->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Post created successfully',
                'data' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Internal Server Error !"
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::where('id', $id)->with('user')->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully retrieved data!',
                'data' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Internal Server Error !"
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'body' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $post = Post::where('id',$id)->first();
            if (empty($post)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => "Post Not Found !"
                ], 404);
            }

            if ($request->title) {
                $post->title = $request->title;
            }
            if ($request->body) {
                $post->body = $request->body;
            }
            $post->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Post updated successfully',
                'data' => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Internal Server Error !"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::where('id', $id)->first();

            if (empty($post)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => "Post Not Found !"
                ], 404);
            }

            if ($post->user_id !== Auth::user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $post->delete();

            return response()->json(['message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Internal Server Error !"
            ], 500);
        }
    }
}
