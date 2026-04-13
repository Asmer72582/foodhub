<?php

namespace Database\Seeders;

use App\Enums\Ask;
use App\Enums\Role as EnumRole;
use App\Enums\Status;
use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Seeder;

class TrialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Trial Admin
        $trialAdmin = User::updateOrCreate(
            ['email' => 'trial@foodhub.com'],
            [
                'name' => 'Trial Admin',
                'phone' => '1234567890',
                'username' => 'trial_admin',
                'email_verified_at' => now(),
                'password' => bcrypt('123456'),
                'branch_id' => 0,
                'status' => Status::ACTIVE,
                'country_code' => '+1',
                'is_guest' => Ask::NO
            ]
        );
        $trialAdmin->assignRole(EnumRole::ADMIN);

        Address::updateOrCreate(
            ['user_id' => $trialAdmin->id, 'label' => 'Trial Office'],
            [
                'address' => '123 Trial Street, Demo City',
                'apartment' => 'Suite 101',
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
            ]
        );

        // Create Trial Customer
        $trialCustomer = User::updateOrCreate(
            ['email' => 'trial_customer@foodoos.com'],
            [
                'name' => 'Trial Customer',
                'phone' => '0987654321',
                'username' => 'trial_customer',
                'email_verified_at' => now(),
                'password' => bcrypt('123456'),
                'branch_id' => 0,
                'status' => Status::ACTIVE,
                'country_code' => '+1',
                'is_guest' => Ask::NO
            ]
        );
        $trialCustomer->assignRole(EnumRole::CUSTOMER);

        Address::updateOrCreate(
            ['user_id' => $trialCustomer->id, 'label' => 'Home'],
            [
                'address' => '456 Trial Lane, Demo City',
                'apartment' => 'Apt 4B',
                'latitude' => '40.7306',
                'longitude' => '-73.9352',
            ]
        );

        // Add some dummy orders for the trial customer
        for ($i = 1; $i <= 5; $i++) {
            $order = \App\Models\Order::create([
                'order_serial_no'  => date('dmy') . 'T' . $i,
                'user_id'          => $trialCustomer->id,
                'branch_id'        => 1,
                'subtotal'         => 50.00,
                'discount'         => 0.00,
                'delivery_charge'  => 0.00,
                'total_tax'        => 0.00,
                'total'            => 50.00,
                'order_type'       => \App\Enums\OrderType::DELIVERY,
                'order_datetime'   => now()->subDays(rand(1, 10)),
                'delivery_time'    => '12:00 - 13:00',
                'preparation_time' => 20,
                'is_advance_order' => \App\Enums\Ask::NO,
                'payment_method'   => 1,
                'payment_status'   => \App\Enums\PaymentStatus::PAID,
                'status'           => \App\Enums\OrderStatus::DELIVERED,
                'source'           => \App\Enums\Source::WEB,
            ]);

            // Add an item to the order
            \App\Models\OrderItem::create([
                'order_id'             => $order->id,
                'branch_id'            => 1,
                'item_id'              => 1,
                'price'                => 50.00,
                'quantity'             => 1,
                'discount'             => 0.00,
                'tax_name'             => 'VAT',
                'tax_rate'             => '0',
                'tax_type'             => 1, // Fixed/Percentage? Assuming 1 for simplicity
                'tax_amount'           => 0.00,
                'item_variations'      => null,
                'item_extras'          => null,
                'item_variation_total' => 0,
                'item_extra_total'     => 0,
                'total_price'          => 50.00,
            ]);
        }
    }
}
