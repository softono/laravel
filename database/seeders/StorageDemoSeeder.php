<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Models\Auth\User;
use App\Services\Storage\ApiCredentialService;
use App\Services\Storage\BucketService;
use Illuminate\Database\Seeder;

/**
 * Optional demo data for the Storage Engine - a sample bucket and API
 * credential for the seeded Bucket Admin (role USER) account from
 * DataSeeder. Not wired into DatabaseSeeder by default (an access
 * key/secret pair is only useful printed to the console once, which
 * `db:seed` alone doesn't surface well) - run explicitly:
 *
 *   php artisan db:seed --class=Database\\Seeders\\StorageDemoSeeder
 */
class StorageDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', UserRole::USER)->first();

        if (! $user) {
            $this->command?->warn('StorageDemoSeeder: no Bucket Admin (role USER) account found - run DataSeeder first.');

            return;
        }

        $bucketService = app(BucketService::class);
        $result = $bucketService->create($user, [
            'name' => 'demo-bucket',
            'visibility' => 'private',
        ]);

        if ($result['status']) {
            $this->command?->info("Created demo bucket 'demo-bucket' for {$user->email}.");
        } else {
            $this->command?->info("Demo bucket already exists: {$result['message']}");
        }

        $credentialService = app(ApiCredentialService::class);
        $credential = $credentialService->create($user);

        $this->command?->info('Created a demo API credential for '.$user->email.':');
        $this->command?->info('  Access Key: '.$credential['api_user']->access_key);
        $this->command?->info('  Secret Key: '.$credential['secret_key'].'  (shown once - save it now)');
    }
}
