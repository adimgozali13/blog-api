<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;


class ApiTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $post;

    #[Test]
    public function it_registers_a_user_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Elona Faranzi',
            'email' => 'elonafaranzi@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'elonafaranzi@gmail.com',
        ]);

        $this->user = User::where('email', 'elonafaranzi@gmail.com')->first();
    }


    #[Test]
    public function it_logs_in_a_user_successfully()
    {
        if (!$this->user) {
            $this->it_registers_a_user_successfully();
        }

        $response = $this->postJson('/api/login', [
            'email' => 'elonafaranzi@gmail.com',
            'password' => 'password123'
        ]);

        $response->assertJson(function (AssertableJson $json) {
            $json->where('status', 'success')
                ->has('access_token')
                ->where('token_type', 'Bearer');
        });

        $token = $response->json('access_token');

        return $token;
    }

    #[Test]
    public function it_creates_a_post_successfully()
    {
        // Mendapatkan token dari fungsi login
        $token = $this->it_logs_in_a_user_successfully();

        // Autentikasi menggunakan Sanctum token
        Sanctum::actingAs($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/posts/store', [
                'title' => 'First Post',
                'body' => 'This is the body of the first post',
            ]);

        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                    ->where('message', 'Post created successfully')
                    ->has('data', function ($json) {
                        $json->where('title', 'First Post')
                            ->where('body', 'This is the body of the first post')
                            ->etc();
                    });
            });

        $this->assertDatabaseHas('posts', [
            'title' => 'First Post',
            'body' => 'This is the body of the first post',
        ]);

        $this->post = Post::first();
    }

    #[Test]
    public function it_retrieves_posts_successfully()
    {
        $token = $this->it_logs_in_a_user_successfully();

        // Menyiapkan data post dengan menggunakan factory
        Post::factory()->count(5)->create(['user_id' => $this->user->id]);

        Sanctum::actingAs($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/posts?paginate=10');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('success', true)
                    ->where('message', 'Success !')
                    ->has('data')
                    ->whereType('data', 'array')
                    ->has('data.data')
                    ->whereType('data.data', 'array')
                    ->etc();
            });
    }


    #[Test]
    public function it_updates_a_post_successfully()
    {
        if (!$this->post) {
            $this->it_creates_a_post_successfully();
        }

        $token = $this->it_logs_in_a_user_successfully();

        Sanctum::actingAs($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/posts/' . $this->post->id, [
                'title' => 'Updated Post Title',
                'body' => 'Updated post body content'
            ]);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                    ->where('message', 'Post updated successfully')
                    ->has('data')
                    ->where('data.title', 'Updated Post Title')
                    ->where('data.body', 'Updated post body content')
                    ->etc();
            });

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Updated Post Title',
            'body' => 'Updated post body content'
        ]);
    }


    #[Test]
    public function it_retrieves_a_single_post_successfully()
    {
        // Pastikan ada pos untuk diuji
        if (!$this->post) {
            $this->it_creates_a_post_successfully();
        }

        $token = $this->it_logs_in_a_user_successfully();

        Sanctum::actingAs($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/posts/' . $this->post->id);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                    ->where('message', 'Successfully retrieved data!')
                    ->has('data')
                    ->where('data.id', $this->post->id)
                    ->where('data.title', $this->post->title)
                    ->where('data.body', $this->post->body)
                    ->where('data.user.id', $this->user->id)
                    ->etc();
            });
    }

    #[Test]
    public function it_deletes_a_post_successfully()
    {
        if (!$this->post) {
            $this->it_creates_a_post_successfully();
        }

        $token = $this->it_logs_in_a_user_successfully();

        Sanctum::actingAs($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/posts/' . $this->post->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Post deleted successfully',
            ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $this->post->id,
        ]);
    }

    #[Test]
public function it_logs_out_a_user_successfully()
{
    $token = $this->it_logs_in_a_user_successfully();

    Sanctum::actingAs($this->user);

    $this->user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);

    $this->assertCount(0, $this->user->tokens);
}

}
