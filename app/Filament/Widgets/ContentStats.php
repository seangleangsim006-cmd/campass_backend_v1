<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;

use App\Models\{Destination, Download, Phrase, Place, PlaceStory, User};
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $publishedPlaces = Place::published()->count();
        $totalPlaces     = Place::count();

        $withoutStory = Place::published()
            ->whereDoesntHave('stories', fn ($q) => $q->where('status', 'published'))
            ->count();

        $inReview = Destination::where('status', 'in_review')->count()
            + Place::where('status', 'in_review')->count()
            + PlaceStory::where('status', 'in_review')->count()
            + Phrase::where('status', 'in_review')->count();

        $stats = [
            Stat::make('Published places', $publishedPlaces)
                ->description("of {$totalPlaces} places in total")
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('success'),

            Stat::make('Places without a story', $withoutStory)
                ->description('Published, but no audio story yet')
                ->color($withoutStory > 0 ? 'warning' : 'success'),

            Stat::make('Waiting for review', $inReview)
                ->description('Content marked "In review"')
                ->color($inReview > 0 ? 'warning' : 'gray'),
        ];

        // user numbers are for the super admin only
        if (auth()->user()?->hasRole('super_admin')) {
            $users    = User::role('user')->count();
            $newUsers = User::role('user')->where('created_at', '>=', now()->subDays(7))->count();

            $stats[] = Stat::make('App users', $users)
                ->description("{$newUsers} new in the last 7 days")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success');

            $stats[] = Stat::make('Offline guide downloads', Download::count())
                ->description('All time')
                ->color('info');

            $stats[] = Stat::make('Saved places', DB::table('saved_places')->count())
                ->description('Hearts across all users')
                ->color('info');
        }

        return $stats;
    }
}