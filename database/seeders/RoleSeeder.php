<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = ['owner', 'employee'];
        foreach($role as $x){
            $save = new Role();
            $save->name = $x;
            $save->save();
        }
    }
}
