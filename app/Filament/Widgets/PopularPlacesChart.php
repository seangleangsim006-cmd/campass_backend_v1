<?php

namespace App\Filament\Widgets;

use App\Models\Place;
use Filament\Widgets\ChartWidget;

class PopularPlacesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return 'Most saved places';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $places = Place::query()
            ->withCount('savedByUsers')
            ->whereHas('savedByUsers')
            ->orderByDesc('saved_by_users_count')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Saves',
                'data'  => $places->pluck('saved_by_users_count')->all(),
            ]],
            'labels' => $places->map(fn ($p) => $p->getTranslation('name', 'en'))->all(),
        ];
    }
}