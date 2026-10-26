<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class NewUsersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'New users, last 30 days';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $counts = User::role('user')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data   = [];

        for ($i = 0; $i < 30; $i++) {
            $date     = $start->copy()->addDays($i);
            $labels[] = $date->format('d M');
            $data[]   = (int) ($counts[$date->toDateString()] ?? 0);   // days without sign-ups show 0
        }

        return [
            'datasets' => [['label' => 'New users', 'data' => $data]],
            'labels'   => $labels,
        ];
    }
}