<?php

namespace DevRajThapa\LaravelTerraDraw\Commands;

use Illuminate\Console\Command;

class LaravelTerraDrawCommand extends Command
{
    public $signature = 'laravel-terra-draw';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
