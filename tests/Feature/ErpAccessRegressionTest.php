<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ErpAccessRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $code): Company
    {
        return Company::create(['company_name' => "Company {$code}", 'company_code' => $code, 'status' => true]);
    }

    private function branch(Company $company, string $code): Store
    {
        return Store::create(['company_id' => $company->id, 'store_name' => $code, 'store_code' => $code, 'status' => true]);
    }

    private function viewer(Company $company, Store $store): User
    {
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->stores()->attach($store->id);
        $permission = Permission::findOrCreate('settings.view', 'web');
        $user->givePermissionTo($permission);
        return $user;
    }

    public function test_assigned_user_can_view_active_branch_settings(): void
    {
        $company = $this->company('A');
        $store = $this->branch($company, 'A1');
        $user = $this->viewer($company, $store);
        $this->actingAs($user)->withSession(['active_company_id' => $company->id, 'active_store_id' => $store->id])
            ->get('/settings/billing-print')->assertOk();
    }

    public function test_another_company_cannot_be_selected_by_forged_context(): void
    {
        $a = $this->company('A'); $a1 = $this->branch($a, 'A1');
        $b = $this->company('B'); $b1 = $this->branch($b, 'B1');
        $user = $this->viewer($a, $a1);
        $this->actingAs($user)->withSession(['active_company_id' => $b->id, 'active_store_id' => $b1->id])
            ->get('/settings/billing-print')->assertForbidden();
    }

    public function test_unassigned_branch_in_same_company_is_denied(): void
    {
        $a = $this->company('A'); $a1 = $this->branch($a, 'A1'); $a2 = $this->branch($a, 'A2');
        $user = $this->viewer($a, $a1);
        $this->actingAs($user)->withSession(['active_company_id' => $a->id, 'active_store_id' => $a2->id])
            ->get('/settings/billing-print')->assertForbidden();
    }

    public function test_view_permission_does_not_allow_settings_update(): void
    {
        $a = $this->company('A'); $store = $this->branch($a, 'A1'); $user = $this->viewer($a, $store);
        $this->actingAs($user)->withSession(['active_company_id' => $a->id, 'active_store_id' => $store->id])
            ->put('/settings/billing-print', ['settings' => ['auto_print' => false]])->assertForbidden();
        $this->assertDatabaseCount('branch_billing_print_settings', 0);
    }

    public function test_revoked_branch_assignment_blocks_existing_session(): void
    {
        $a = $this->company('A'); $store = $this->branch($a, 'A1'); $user = $this->viewer($a, $store);
        $user->stores()->detach($store->id);
        $this->actingAs($user)->withSession(['active_company_id' => $a->id, 'active_store_id' => $store->id])
            ->get('/settings/billing-print')->assertForbidden();
    }

    public function test_inactive_user_cannot_use_personal_profile(): void
    {
        $user = User::factory()->create(['status' => false]);
        $this->actingAs($user)->get('/profile')->assertForbidden();
    }

    public function test_inactive_super_user_cannot_use_existing_session(): void
    {
        $user = User::factory()->create(['status' => false, 'is_super_user' => true]);
        $this->actingAs($user)->get('/super-admin/dashboard')->assertForbidden();
    }

    public function test_inactive_user_can_still_logout(): void
    {
        $user = User::factory()->create(['status' => false]);
        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_super_user_can_open_own_profile_without_business_context(): void
    {
        $user = User::factory()->create(['is_super_user' => true]);
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_guest_cannot_access_business_settings(): void
    {
        $this->get('/settings/billing-print')->assertRedirect('/login');
    }

    public function test_password_reset_routes_are_registered_once(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());
        $this->assertCount(1, $routes->where('uri', 'forgot-password')->filter(fn ($route) => in_array('GET', $route->methods())));
        $this->assertCount(1, $routes->where('uri', 'forgot-password')->filter(fn ($route) => in_array('POST', $route->methods())));
    }
    public function test_public_diagnostic_scripts_are_not_shipped(): void
    {
        $this->assertFileDoesNotExist(public_path('phpinfo.php'));
        $this->assertFileDoesNotExist(public_path('test.php'));
    }

}
