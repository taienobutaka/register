<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 会員登録画面が正常に表示されるかテスト
     */
    public function test_registration_form_is_displayed(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * 有効なデータで会員登録が成功するかテスト
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => '会員登録が完了しました。',
        ]);

        // データベースに保存されているか確認
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // パスワードがハッシュ化されているか確認
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * 名前が空の場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_name_is_empty(): void
    {
        $userData = [
            'name' => '',
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * 名前が255文字を超える場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_name_is_too_long(): void
    {
        $userData = [
            'name' => str_repeat('a', 256), // 256文字
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * メールアドレスが空の場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_email_is_empty(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * 無効なメールアドレス形式の場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_email_is_invalid(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * 既に存在するメールアドレスの場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_email_already_exists(): void
    {
        // 既存ユーザーを作成
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => $this->faker->name,
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * パスワードが空の場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_password_is_empty(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * パスワードが8文字未満の場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_password_is_too_short(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '1234567', // 7文字
            'password_confirmation' => '1234567',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * パスワード確認が一致しない場合のバリデーションエラーテスト
     */
    public function test_registration_fails_when_password_confirmation_does_not_match(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * 複数のバリデーションエラーが同時に発生する場合のテスト
     */
    public function test_registration_fails_with_multiple_validation_errors(): void
    {
        $userData = [
            'name' => '', // 空の名前
            'email' => 'invalid-email', // 無効なメール
            'password' => '123', // 短すぎるパスワード
            'password_confirmation' => 'different', // 一致しないパスワード確認
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * レスポンスにユーザー情報が含まれているかテスト
     */
    public function test_registration_response_contains_user_data(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ]
        ]);

        $responseData = $response->json();
        $this->assertEquals('テストユーザー', $responseData['user']['name']);
        $this->assertEquals('test@example.com', $responseData['user']['email']);
        $this->assertArrayNotHasKey('password', $responseData['user']); // パスワードが含まれていないことを確認
    }

    /**
     * 日本語の名前での登録テスト
     */
    public function test_user_can_register_with_japanese_name(): void
    {
        $userData = [
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
        ]);
    }
}
