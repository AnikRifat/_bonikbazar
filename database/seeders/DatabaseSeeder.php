<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call([
            InstallationSeeder::class,
        ]);

        Role::updateOrCreate(['name' => 'User']);

        $user = User::updateOrCreate(['id' => 1], [
            'id'       => 1,
            'name'     => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);
        $user->syncRoles('Super Admin');
        Language::updateOrInsert(
            ['id' => 1],
            [
                'name'       => 'English',
                'code'       => 'en',
                'app_file'   => 'en.json',
                'panel_file' => 'en.json',
            ],
        );

        $settings = [
            ['name' => 'currency_symbol', 'value' => '$', 'type' => 'string'],
            ['name' => 'ios_version', 'value' => '1.0.0', 'type' => 'string'],
            ['name' => 'default_language', 'value' => 'en', 'type' => 'string'],
            ['name' => 'force_update', 'value' => '0', 'type' => 'string'],
            ['name' => 'android_version', 'value' => '1.0.0', 'type' => 'string'],
            ['name' => 'number_with_suffix', 'value' => '0', 'type' => 'string'],
            ['name' => 'maintenance_mode', 'value' => 0, 'type' => 'string'],
            ['name' => 'privacy_policy', 'value' => '', 'type' => 'string'],
            ['name' => 'terms_conditions', 'value' => '', 'type' => 'string'],
            ['name' => 'about_us', 'value' => '1.0.0', 'type' => 'string'],
            ['name' => 'company_tel1', 'value' => '', 'type' => 'string'],
            ['name' => 'company_tel2', 'value' => '', 'type' => 'string'],
            ['name' => 'system_version', 'value' => '1.0.0', 'type' => 'string'],
            ['name' => 'company_email', 'value' => '', 'type' => 'string'],
            ['name' => 'company_logo', 'value' => 'assets/images/logo/sidebar_logo.png', 'type' => 'file'],
            ['name' => 'favicon_icon', 'value' => 'assets/images/logo/favicon.png', 'type' => 'file'],
            ['name' => 'login_image', 'value' => 'assets/images/bg/login.jpg', 'type' => 'file'],
            ['name' => 'place_api_key', 'value' => '1.0.0', 'type' => 'string'],
        ];


        Setting::upsert($settings, ['name'], ['value', 'type']);
    }
}
