<?php

declare(strict_types=1);

namespace App\Service;

class PaginationService
{
    public function calculateTotalPages(int $totalMedias, int $limit)
    {
        return (int) ceil($totalMedias / $limit);
    }

}
