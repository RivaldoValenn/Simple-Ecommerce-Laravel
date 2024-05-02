<?php

namespace App\Traits;

trait RedirectIndex
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}