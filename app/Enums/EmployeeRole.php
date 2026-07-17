<?php

namespace App\Enums;

enum EmployeeRole: string
{
    case Helper = 'helper';
    case ScraperHelper = 'scraper_helper';
    case Kariger = 'kariger';
    case Master = 'master';

    public function label(): string
    {
        return match ($this) {
            self::Helper => 'Helper',
            self::ScraperHelper => 'Scraper Helper',
            self::Kariger => 'Kariger',
            self::Master => 'Master',
        };
    }
}
