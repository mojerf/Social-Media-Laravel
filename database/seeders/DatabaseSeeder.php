<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        $posts = Post::factory(100)->recycle($users)->create();

        Comment::factory(200)->recycle($users)->recycle($posts)->create();
        Like::factory(200)->recycle($users)->recycle($posts)->create();

        User::factory()
        ->has(Post::factory(10))
        ->has(Comment::factory(50)->recycle($posts))
        ->has(Like::factory(50)->recycle($posts))
        ->create([
            'username'=>'mojerf',
        ]);
    }
}
