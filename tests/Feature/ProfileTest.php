<?php

namespace Tests\Feature;

use App\Tenant;
use App\User;
use Hyn\Tenancy\Environment;
use Tests\TenantAwareTestCase;

class ProfileTest extends TenantAwareTestCase
{
    // Checkout the complete tests here: https://github.com/ashokgelal/townhouse/blob/da862a5abd5e8411beaab14b6b643c83eed0c5eb/tests/Feature/ProfileTest.php

    /** @test */
    public function must_be_authenticated_to_update_profile()
    {
        $this->createUserInTenant();
        $this->update(['name' => 'new name', 'email' => 'new email'])->assertRedirect('/login');
    }

    /** @test */
    public function user_information_is_udpated_in_database()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);

        $this->update(['name' => 'new name', 'email' => 'new email']);
        $this->assertDatabaseHas('users', ['name' => 'new name', 'email' => 'new email']);
    }

    /** @test */
    public function user_is_updated_only_in_tenants_own_database()
    {
        $user1 = $this->createUserInTenant([], 'tenant1');
        $user2 = $this->createUserInTenant([], 'tenant2');

        $this->withoutExceptionHandling();
        // update user from the first tenant
        $this->switchTenant($this->tenants[0])->signIn($user1);
        $this->update(['email' => 'newemail@tenant.com', 'name' => $user1->name]);
        $this->assertDatabaseHas('users', ['email' => 'newemail@tenant.com', 'name' => $user1->name]);

        // shouldn't update user from the second tenant
        $this->switchTenant($this->tenants[1]);
        $this->assertDatabaseHas('users', ['email' => $user2->email, 'name' => $user2->name]);
    }
    
    // Checkout the complete tests here: https://github.com/ashokgelal/townhouse/blob/da862a5abd5e8411beaab14b6b643c83eed0c5eb/tests/Feature/ProfileTest.php
}