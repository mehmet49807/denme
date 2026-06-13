<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PremiumSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::create([
            'username'   => 'admin',
            'first_name' => 'Sistem',
            'last_name'  => 'Yöneticisi',
            'email'      => 'admin@gonulkoprusu.test',
            'phone'      => '+90000000000',
            'password'   => Hash::make('password'),
            'gender'     => 'male',
            'city'       => 'İstanbul',
            'district'   => 'Kadıköy',
            'role'       => 'admin',
        ]);

        // Sample woman (free full access)
        $ayse = User::create([
            'username'   => 'ayse',
            'first_name' => 'Ayşe',
            'last_name'  => 'Yılmaz',
            'email'      => 'ayse@gonulkoprusu.test',
            'phone'      => '+90500000001',
            'password'   => Hash::make('password'),
            'gender'     => 'female',
            'city'       => 'İzmir',
            'district'   => 'Karşıyaka',
        ]);

        // Sample premium man
        $mehmet = User::create([
            'username'   => 'mehmet',
            'first_name' => 'Mehmet',
            'last_name'  => 'Demir',
            'email'      => 'mehmet@gonulkoprusu.test',
            'phone'      => '+90500000002',
            'password'   => Hash::make('password'),
            'gender'     => 'male',
            'city'       => 'Ankara',
            'district'   => 'Çankaya',
            'is_premium' => true,
        ]);

        $mehmet->subscriptions()->create([
            'package_type' => 'platinum',
            'price'        => PremiumSubscription::PACKAGES['platinum']['price'],
            'started_at'   => now(),
            'expires_at'   => now()->addDays(30),
            'is_active'    => true,
        ]);

        Post::create([
            'user_id'   => $ayse->id,
            'image_url' => 'https://placehold.co/600x600/e3a9a1/fffdf9?text=Gonul+Koprusu',
            'caption'   => 'Merhaba dünya!',
        ]);
    }
}
