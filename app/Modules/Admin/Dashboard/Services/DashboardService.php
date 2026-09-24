<?php

namespace App\Modules\Admin\Dashboard\Services;

use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Repositories\Auth\UserRepository;
use Carbon\CarbonImmutable;

class DashboardService
{
    public function __construct(protected UserRepository $users) {}

    /**
     * @return array{total: int, active: int, inactive: int}
     */
    public function counts(): array
    {
        return [
            'total' => $this->users->countByRole(UserRole::USER),
            'active' => $this->users->countByRole(UserRole::USER, UserStatus::ACTIVE),
            'inactive' => $this->users->countByRole(UserRole::USER, UserStatus::INACTIVE),
        ];
    }

    /**
     * New end-user sign-ups per day (last 7 days) or per month (last 6 or 12).
     *
     * @return array{label: string[], data: int[]}
     */
    public function userChart(string $period): array
    {
        $daily = $period === 'day';
        $steps = match ($period) {
            'day' => 7,
            'month' => 6,
            default => 12,
        };
        $unit = $daily ? 'day' : 'month';
        $key = $daily ? 'Y-m-d' : 'Y-m';

        $start = $daily
            ? CarbonImmutable::today()->subDays($steps - 1)
            : CarbonImmutable::today()->startOfMonth()->subMonths($steps - 1);

        $counts = $this->users
            ->signupsSince(UserRole::USER, $start, $daily ? '%Y-%m-%d' : '%Y-%m')
            ->pluck('count', 'label');

        $labels = [];
        $data = [];
        for ($i = 0; $i < $steps; $i++) {
            $at = $start->add($i, $unit);
            $labels[] = $daily ? $at->format('D d M') : $at->format('M Y');
            $data[] = (int) ($counts[$at->format($key)] ?? 0);
        }

        return ['label' => $labels, 'data' => $data];
    }
}
