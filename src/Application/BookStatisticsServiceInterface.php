<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\DTO\BookStatisticsDTO;

interface BookStatisticsServiceInterface
{
    public function getStatistics(): BookStatisticsDTO;
}
