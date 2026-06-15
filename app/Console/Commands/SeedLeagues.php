<?php

namespace App\Console\Commands;

use App\Models\League;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedLeagues extends Command
{
    protected $signature = 'prices:seed';
    protected $description = 'Seed the current PoE2 leagues';

    public function handle(): int
    {
        $leagues = [
            ['name' => 'Runes of Aldur', 'realm' => 'poe2', 'is_current' => true],
            ['name' => 'HC Runes of Aldur', 'realm' => 'poe2', 'is_current' => false],
            ['name' => 'Standard', 'realm' => 'poe2', 'is_current' => false],
        ];

        foreach ($leagues as $data) {
            League::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name'])])
            );
        }

        $this->info('Leagues seeded. Current: Runes of Aldur');
        return self::SUCCESS;
    }
}
