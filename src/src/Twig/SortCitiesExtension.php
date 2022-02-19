<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SortCitiesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sort_cities', [$this, 'sort']),
        ];
    }

    public function sort(array $array): array
    {
        usort($array, static fn($a, $b) => $a['name'] <=> $b['name']);

        return $array;
    }
}
