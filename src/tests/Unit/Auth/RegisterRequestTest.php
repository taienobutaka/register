<?php

namespace Tests\Unit\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なデータでバリデーションが通るかテスト
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * 名前が必須であることをテスト
     */
    public function test_name_is_required(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * 名前の最大文字数制限をテスト
     */
    public function test_name_max_length_validation(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => str_repeat('a', 256), // 256文字
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * メールアドレスが必須であることをテスト
     */
    public function test_email_is_required(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * メールアドレスの形式チェックをテスト
     */
    public function test_email_format_validation(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * パスワードが必須であることをテスト
     */
    public function test_password_is_required(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * パスワードの最小文字数制限をテスト
     */
    public function test_password_min_length_validation(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567', // 7文字
            'password_confirmation' => '1234567',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * パスワード確認フィールドの一致をテスト
     */
    public function test_password_confirmation_validation(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * authorize メソッドが true を返すことをテスト
     */
    public function test_authorize_returns_true(): void
    {
        $request = new RegisterRequest();
        
        $this->assertTrue($request->authorize());
    }
}
