<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessOnboardingRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_registration_cannot_grant_super_user_access(): void
    {
        \Spatie\Permission\Models\Role::create([
            'name' => 'Company Admin', 'guard_name' => 'web', 'company_id' => null,
        ]);
        $this->post('/register-business', [
            'name' => 'Review Owner', 'email' => 'review@example.test', 'mobile' => '9000000000',
            'password' => 'Review12345', 'password_confirmation' => 'Review12345',
            'company_name' => 'Review Company', 'company_email' => 'company@example.test',
            'company_phone' => '9000000000', 'store_name' => 'Main Branch',
            'contact_person' => 'Review Owner', 'store_phone' => '9000000000',
            'store_email' => 'branch@example.test', 'address' => 'Test address', 'city' => 'Test City',
            'state' => 'Tamil Nadu', 'country' => 'India', 'pincode' => '600001',
            'terms' => true, 'is_super_user' => true, 'status' => true,
        ])->assertSessionHasNoErrors()->assertRedirect('/login');

        $user = User::where('email', 'review@example.test')->firstOrFail();
        $this->assertFalse($user->is_super_user);
        $this->assertFalse($user->status);
        $company = Company::findOrFail($user->company_id);
        $this->assertFalse($company->status);
        $this->assertFalse(Store::findOrFail($user->store_id)->status);
        $this->assertGuest();
    }
}
