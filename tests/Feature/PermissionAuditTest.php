<?php

namespace Tests\Feature;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionAuditTest extends TestCase
{
    /** Every admin route is either self-service or guarded by a key that exists in the permission list. */
    public function test_every_admin_route_maps_to_a_known_permission(): void
    {
        $permissions = new PermissionService;
        $known = $permissions->getPermissionList();
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'admin/') || str_starts_with($uri, 'admin/auth/')) {
                continue;
            }

            foreach ([false, true] as $hasId) {
                $keys = $permissions->requiredKeys($uri, $hasId);

                if ($keys === null) {
                    continue;
                }

                $this->assertNotEmpty(array_intersect($keys, $known), "{$uri} is not guarded by a listed permission (got ".implode(',', $keys).')');
            }

            $checked++;
        }

        $this->assertGreaterThan(30, $checked);
    }

    public function test_unlisted_keys_are_refused(): void
    {
        $permissions = new PermissionService;

        $this->assertFalse($permissions->hasPermission('admin/nonexistent', 'admin/nonexistent'));
        $this->assertTrue($permissions->hasPermission($permissions->requiredKeys('admin/user/list'), 'admin/user'));
        $this->assertFalse($permissions->hasPermission($permissions->requiredKeys('admin/user/save'), 'admin/user'));
        $this->assertTrue($permissions->hasPermission(['admin/blog', 'admin/user'], 'admin/user'));
    }

    public function test_save_needs_create_or_update_depending_on_the_id(): void
    {
        $permissions = new PermissionService;

        $this->assertSame(['admin/blog/create'], $permissions->requiredKeys('admin/blog/save', false));
        $this->assertSame(['admin/blog/update'], $permissions->requiredKeys('admin/blog/save', true));
        $this->assertSame(['admin/setting/update'], $permissions->requiredKeys('admin/setting/save', false));
        $this->assertNull($permissions->requiredKeys('admin/account/update', false));
    }
}
