<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batchSize = 1000;
        $totalRecords = 100000;

        for ($i = 0; $i < $totalRecords; $i += $batchSize) {
            $users = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $users[] = [
                    'name' => 'name_'.($i + $j),
                    'email' => 'email_'.($i + $j).'@example.com',
                    'password' => 'Password',
                ];
            }
            echo('Inserted '.($i + $batchSize).' records'.PHP_EOL);
            User::insert($users);
        }
        dd('DONE');
    }
}
