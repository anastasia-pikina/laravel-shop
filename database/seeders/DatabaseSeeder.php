<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        $permissions = [
            'create-post',
            'edit-post',
            'delete-post',
            'view-post',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to admin
        $adminRole->syncPermissions($permissions);

        // Assign limited permissions to editor
        $editorRole->syncPermissions(['create-post', 'edit-post', 'view-post']);

        // Assign default user role
        $user = User::create([
            'name' => 'Admin2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('123456'),
        ]);
        $user->assignRole('admin');
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Admin',
//            'email' => 'admin@example.com',
//            'password' => bcrypt('123456'),
//        ]);
    }
}
