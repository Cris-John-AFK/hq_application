<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\InventoryItem::create([
            'name' => 'MacBook Pro 16" (M3 Max)',
            'type' => 'Laptop',
            'quantity' => 12,
            'serial_number' => 'MBP-2024-X01',
            'status' => 'Good',
            'location' => 'Tech Hub',
            'description' => 'High-end developer workstations'
        ]);

        \App\Models\InventoryItem::create([
            'name' => 'Logitech MX Master 3S',
            'type' => 'Mouse',
            'quantity' => 25,
            'serial_number' => 'LOGI-MX-099',
            'status' => 'Good',
            'location' => 'Storage A',
            'description' => 'Wireless ergonomic mice'
        ]);

        \App\Models\InventoryItem::create([
            'name' => 'HP LaserJet Enterprise',
            'type' => 'Printer',
            'quantity' => 4,
            'serial_number' => 'HP-LJ-900',
            'status' => 'Needs Repair',
            'location' => 'Main Office',
            'description' => 'High-volume network printer'
        ]);

        \App\Models\InventoryItem::create([
            'name' => 'Dell UltraSharp 32" 4K',
            'type' => 'Monitor',
            'quantity' => 15,
            'serial_number' => 'DELL-U-4K-21',
            'status' => 'Good',
            'location' => 'Design Studio',
            'description' => 'Professional color-accurate monitors'
        ]);

        \App\Models\InventoryItem::create([
            'name' => 'Steelcase Gesture Chair',
            'type' => 'Furniture',
            'quantity' => 30,
            'serial_number' => 'STC-GES-01',
            'status' => 'Good',
            'location' => 'Main Office',
            'description' => 'Premium ergonomic office chairs'
        ]);

        \App\Models\InventoryItem::create([
            'name' => 'Logitech Brio 4K',
            'type' => 'Webcam',
            'quantity' => 20,
            'serial_number' => 'LOGI-BRI-05',
            'status' => 'Good',
            'location' => 'IT Room',
            'description' => 'Ultra HD streaming webcams'
        ]);
    }
}
