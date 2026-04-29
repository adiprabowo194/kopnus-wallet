<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'member_code' => 'MBR-0001',
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'status' => 'active',
                'balance' => 500000.00
            ],
            [
                'member_code' => 'MBR-0002',
                'name' => 'Siti Rahayu',
                'email' => 'siti@example.com',
                'status' => 'active',
                'balance' => 1250000.00
            ],
            [
                'member_code' => 'MBR-0003',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
                'status' => 'active',
                'balance' => 0.00
            ],
            [
                'member_code' => 'MBR-0004',
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'status' => 'inactive',
                'balance' => 750000.00
            ],
        ];

        foreach ($members as $data) {
            Member::updateOrCreate(
                ['member_code' => $data['member_code']],
                $data
            );
        }
    }
}
