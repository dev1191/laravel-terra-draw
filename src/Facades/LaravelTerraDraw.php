<?php

namespace DevRajThapa\LaravelTerraDraw\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DevRajThapa\LaravelTerraDraw\LaravelTerraDraw
 */
class LaravelTerraDraw extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DevRajThapa\LaravelTerraDraw\LaravelTerraDraw::class;
    }
}
