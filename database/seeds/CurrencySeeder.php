<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->updateOrInsert(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$']
        );
    }
}
