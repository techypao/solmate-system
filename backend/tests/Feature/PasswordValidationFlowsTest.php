<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordValidationFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        Notification::fake();
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'missing uppercase and special character' => [
                'password123',
                'Password must contain at least one uppercase letter.',
            ],
            'missing special character' => [
                'Password123',
                'Password must contain at least one special character.',
            ],
            'missing uppercase letter' => [
                'password123!',
                'Password must contain at least one uppercase letter.',
            ],
            'too short' => [
                'Pass!',
                'Password must be at least 8 characters.',
            ],
        ];
    }

    public static function invalidContactNumberProvider(): array
    {
        return [
            'missing leading digit with only 10 numbers' => [
                '9123456789',
            ],
            'too many digits' => [
                '091234567890',
            ],
            'contains letters' => [
                '09123abc789',
            ],
        ];
    }

    #[DataProvider('invalidPasswordProvider')]
    public function test_api_registration_rejects_weak_passwords(string $password, string $message): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'API',
            'last_name' => 'Customer',
            'email' => 'api_customer@example.com',
            'address' => '123 Solar Street',
            'contact_number' => '09171234567',
            'password' => $password,
            'password_confirmation' => $password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password')
            ->assertJsonPath('errors.password.0', $message);
    }

    public function test_api_registration_accepts_a_compliant_password(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'API',
            'last_name' => 'Customer',
            'email' => 'api_customer_valid@example.com',
            'address' => '123 Solar Street',
            'contact_number' => '09171234567',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Registered successfully. Please verify your email.');

        $this->assertDatabaseHas('users', [
            'email' => 'api_customer_valid@example.com',
            'role' => User::ROLE_CUSTOMER,
            'first_name' => 'API',
            'last_name' => 'Customer',
        ]);
    }

    #[DataProvider('invalidContactNumberProvider')]
    public function test_api_registration_rejects_invalid_contact_numbers(string $contactNumber): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'API',
            'last_name' => 'Customer',
            'email' => 'api_customer_contact_invalid@example.com',
            'address' => '123 Solar Street',
            'contact_number' => $contactNumber,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contact_number')
            ->assertJsonPath('errors.contact_number.0', 'Contact number must be exactly 11 digits.');
    }

    public function test_api_registration_accepts_an_eleven_digit_contact_number(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'API',
            'last_name' => 'Customer',
            'email' => 'api_customer_contact_valid@example.com',
            'address' => '123 Solar Street',
            'contact_number' => '09123456789',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Registered successfully. Please verify your email.');
    }

    public function test_api_registration_allows_an_empty_landline_number(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'API',
            'last_name' => 'Customer',
            'email' => 'api_customer_landline_optional@example.com',
            'address' => '123 Solar Street',
            'contact_number' => '09123456789',
            'landline_number' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Registered successfully. Please verify your email.');

        $this->assertDatabaseHas('users', [
            'email' => 'api_customer_landline_optional@example.com',
            'landline_number' => null,
        ]);
    }

    public function test_customer_profile_update_ignores_name_fields_and_updates_landline(): void
    {
        $customer = User::query()->create([
            'name' => 'Original Customer',
            'first_name' => 'Original',
            'last_name' => 'Customer',
            'email' => 'customer_profile_update@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_CUSTOMER,
            'address' => '123 Solar Street',
            'contact_number' => '09171234567',
            'landline_number' => null,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $customer->createToken('auth_token');

        Sanctum::actingAs($customer);

        $this->putJson('/api/customer/account', [
            'name' => 'Changed Name',
            'first_name' => 'Changed',
            'last_name' => 'Person',
            'email' => 'customer_profile_updated@example.com',
            'address' => '456 Updated Avenue',
            'contact_number' => '09991234567',
            'landline_number' => '(02) 8123-4567',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'FORCE_LOGOUT_EMAIL_CHANGED')
            ->assertJsonPath('error', 'You changed your email. Please verify and log in again.');

        $customer->refresh();

        $this->assertSame('Original Customer', $customer->name);
        $this->assertSame('Original', $customer->first_name);
        $this->assertSame('Customer', $customer->last_name);
        $this->assertSame('customer_profile_updated@example.com', $customer->email);
        $this->assertSame('456 Updated Avenue', $customer->address);
        $this->assertSame('09991234567', $customer->contact_number);
        $this->assertSame('(02) 8123-4567', $customer->landline_number);
        $this->assertNull($customer->email_verified_at);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $customer->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_technician_profile_update_ignores_name_fields_and_updates_email(): void
    {
        $technician = User::query()->create([
            'name' => 'Original Technician',
            'first_name' => 'Original',
            'last_name' => 'Technician',
            'email' => 'technician_profile_update@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_TECHNICIAN,
        ]);
        $technician->forceFill(['email_verified_at' => now()])->save();
        $technician->createToken('auth_token');

        Sanctum::actingAs($technician);

        $this->putJson('/api/technician/account', [
            'name' => 'Changed Technician',
            'first_name' => 'Changed',
            'last_name' => 'Person',
            'email' => 'technician_profile_updated@example.com',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'FORCE_LOGOUT_EMAIL_CHANGED')
            ->assertJsonPath('error', 'You changed your email. Please verify and log in again.');

        $technician->refresh();

        $this->assertSame('Original Technician', $technician->name);
        $this->assertSame('Original', $technician->first_name);
        $this->assertSame('Technician', $technician->last_name);
        $this->assertSame('technician_profile_updated@example.com', $technician->email);
        $this->assertNull($technician->email_verified_at);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $technician->id,
            'tokenable_type' => User::class,
        ]);
    }

    #[DataProvider('invalidPasswordProvider')]
    public function test_customer_password_update_rejects_weak_passwords(string $password, string $message): void
    {
        $customer = User::query()->create([
            'name' => 'Customer Password Update',
            'email' => 'customer_password_update@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        Sanctum::actingAs($customer);

        $this->putJson('/api/customer/account/password', [
            'current_password' => 'Current123!',
            'new_password' => $password,
            'new_password_confirmation' => $password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('new_password')
            ->assertJsonPath('errors.new_password.0', $message);
    }

    public function test_customer_password_update_accepts_a_compliant_password(): void
    {
        $customer = User::query()->create([
            'name' => 'Customer Password Update',
            'email' => 'customer_password_update_valid@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        Sanctum::actingAs($customer);

        $this->putJson('/api/customer/account/password', [
            'current_password' => 'Current123!',
            'new_password' => 'Password123!',
            'new_password_confirmation' => 'Password123!',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Password updated successfully.');

        $customer->refresh();

        $this->assertTrue(Hash::check('Password123!', $customer->password));
    }

    #[DataProvider('invalidPasswordProvider')]
    public function test_technician_password_update_rejects_weak_passwords(string $password, string $message): void
    {
        $technician = User::query()->create([
            'name' => 'Technician Password Update',
            'email' => 'technician_password_update@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_TECHNICIAN,
        ]);
        $technician->forceFill(['email_verified_at' => now()])->save();

        Sanctum::actingAs($technician);

        $this->putJson('/api/technician/account/password', [
            'current_password' => 'Current123!',
            'new_password' => $password,
            'new_password_confirmation' => $password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('new_password')
            ->assertJsonPath('errors.new_password.0', $message);
    }

    #[DataProvider('invalidPasswordProvider')]
    public function test_admin_profile_password_update_rejects_weak_passwords(string $password, string $message): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Password Update',
            'email' => 'admin_password_update@example.com',
            'password' => 'Current123!',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.profile.show'))
            ->put(route('admin.profile.password.update'), [
                'current_password' => 'Current123!',
                'new_password' => $password,
                'new_password_confirmation' => $password,
            ])
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHasErrors([
                'new_password' => $message,
            ]);
    }
}
