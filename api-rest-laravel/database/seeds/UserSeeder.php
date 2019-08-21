<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'name' => 'admin',
            'surname' => 'pajaro',
            'email' => 'cjmar87@gmail.com',
            'password' => hash('sha256','123456'),
            'role' => 'ROLE_ADMIN',
            'description' => 'Administrador',
            'image' => '',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
