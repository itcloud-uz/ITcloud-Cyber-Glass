<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Web CRM / Dashboard (Obsidian v1)',
                'slug' => 'web_crm',
                'base_price' => 1500,
                'max_price' => 5000,
                'min_days' => 14,
                'icon' => 'fa-laptop-code'
            ],
            [
                'name' => 'Telegram AI Agent (Genetix Bot)',
                'slug' => 'tg_bot',
                'base_price' => 1200,
                'max_price' => 4500,
                'min_days' => 7,
                'icon' => 'fa-brands fa-telegram'
            ],
            [
                'name' => 'Mobile App (iOS/Android)',
                'slug' => 'mobile_app',
                'base_price' => 2500,
                'max_price' => 10000,
                'min_days' => 30,
                'icon' => 'fa-mobile-screen'
            ],
            [
                'name' => 'Kiber-Xavfsizlik (FaceID Scan)',
                'slug' => 'face_id',
                'base_price' => 800,
                'max_price' => 3000,
                'min_days' => 10,
                'icon' => 'fa-fingerprint'
            ],
            [
                'name' => 'Payment Systems (Payme/Click)',
                'slug' => 'payments',
                'base_price' => 900,
                'max_price' => 2500,
                'min_days' => 5,
                'icon' => 'fa-credit-card'
            ],
        ];

        foreach ($services as $service) {
            \App\Models\PriceService::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
