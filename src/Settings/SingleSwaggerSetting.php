<?php

namespace SingleSoftware\SinglesSwagger\Settings;

use Spatie\LaravelSettings\Settings;

class SingleSwaggerSetting extends Settings
{
    public bool $is_preconfigured;

    public static function group(): string
    {
        return 'single-swagger';
    }
}
