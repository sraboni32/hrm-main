<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatRoom;
use App\Models\User;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first user (admin) to create rooms
        $admin = User::first();
        
        if ($admin) {
            // Create a general chat room
            $generalRoom = ChatRoom::create([
                'name' => 'General Discussion',
                'description' => 'General chat room for all employees',
                'type' => 'public',
                'created_by' => $admin->id,
                'is_active' => true,
                'last_activity' => now()
            ]);

            // Add admin as member
            $generalRoom->addMember($admin->id, 'admin');

            // Add other users as members
            $users = User::where('id', '!=', $admin->id)->take(5)->get();
            foreach ($users as $user) {
                $generalRoom->addMember($user->id, 'member');
            }

            // Create a department-specific room
            $deptRoom = ChatRoom::create([
                'name' => 'HR Department',
                'description' => 'Chat room for HR department discussions',
                'type' => 'department',
                'created_by' => $admin->id,
                'is_active' => true,
                'last_activity' => now()
            ]);

            // Add admin as member
            $deptRoom->addMember($admin->id, 'admin');

            echo "Chat rooms created successfully!\n";
        } else {
            echo "No users found. Please create users first.\n";
        }
    }
}
