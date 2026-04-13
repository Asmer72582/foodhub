<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Enums\Role as EnumRole;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-to-basic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all dynamic data from the database while keeping Admin, Chef, and basic configuration.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->confirm('This will delete almost all data in your database. Are you sure you want to proceed?')) {
            return Command::FAILURE;
        }

        $this->info('Starting database reset...');

        Schema::disableForeignKeyConstraints();

        $tablesToClear = [
            'orders',
            'order_items',
            'order_coupons',
            'order_addresses',
            'transactions',
            'push_notifications',
            'messages',
            'message_histories',
            'capture_payment_notifications',
            'notification_alerts',
            'subscribers',
            'sliders',
            'item_categories',
            'items',
            'item_attributes',
            'item_variations',
            'item_extras',
            'item_addons',
            'addons',
            'coupons',
            'offers',
            'offer_items',
            'analytics',
            'analytics_sections',
            'otps',
            'dining_tables',
            'addresses',
            'notifications',
            'failed_jobs',
            'personal_access_tokens',
            'media',
            'password_resets',
        ];

        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                $this->info("Truncating table: $table");
                DB::table($table)->truncate();
            }
        }

        // Handle Users table specifically
        $this->info('Cleaning users table (keeping Admin, Chef, and Walking Customer)...');
        
        $adminRole    = 1; // EnumRole::ADMIN
        $chefRole     = 5; // EnumRole::CHEF
        $customerRole = 2; // EnumRole::CUSTOMER

        // Get model_id column name from Spatie config if possible, fallback to model_id
        $modelIdColumn = config('permission.column_names.model_morph_key', 'model_id');

        // Identify users to keep by role
        $keptUserIdsByRole = DB::table('model_has_roles')
            ->whereIn('role_id', [$adminRole, $chefRole])
            ->pluck($modelIdColumn)
            ->toArray();

        // Identify Walking Customer by email
        $walkingCustomerEmail = 'walkingcustomer@example.com';
        $walkingCustomer = User::where('email', $walkingCustomerEmail)->first();
        
        if ($walkingCustomer) {
            $keptUserIdsByRole[] = $walkingCustomer->id;
        }

        // Delete users not in the keep list
        User::whereNotIn('id', $keptUserIdsByRole)->delete();
        
        // Clean model_has_roles to only keep roles for existing users
        DB::table('model_has_roles')
            ->whereNotIn($modelIdColumn, $keptUserIdsByRole)
            ->delete();

        // Ensure Walking Customer exists (recreate if deleted somehow)
        if (!$walkingCustomer) {
            $this->info('Creating default Walking Customer...');
            $walkingCustomer = User::create([
                'name'              => 'Walking Customer',
                'email'             => $walkingCustomerEmail,
                'phone'             => '125444455',
                'username'          => 'default-customer',
                'email_verified_at' => now(),
                'password'          => bcrypt('123456'),
                'branch_id'         => 0,
                'status'            => 5, // Status::ACTIVE
                'country_code'      => '+880',
                'is_guest'          => 10 // Ask::NO
            ]);
            $walkingCustomer->assignRole($customerRole);
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Database has been reset successfully!');
        $this->info('Kept users: ' . User::count());

        return Command::SUCCESS;
    }
}
