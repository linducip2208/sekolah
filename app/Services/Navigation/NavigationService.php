<?php

namespace App\Services\Navigation;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Resolves the domain navigation IA (config/navigation.php) for the
 * current user: role-aware groups, dead-route filtering, badge counts,
 * command-palette flattening and breadcrumb mapping.
 *
 * Presentation only — server-side authorization stays in middleware
 * and policies. Every query is school-scoped.
 */
class NavigationService
{
    /** Cache TTL for badge counts (seconds). */
    protected int $badgeTtl = 60;

    /**
     * Domain groups visible to the given user.
     *
     * @return array<int, array{key:string,label:string,icon:string,items:array<int,array>>}
     */
    public function groupsFor($user): array
    {
        if (!$user) {
            return [];
        }

        $role = $user->getRoleNames()->first() ?? 'admin';
        $schoolId = $user->school_id;
        $cfg = config('navigation');
        $hasFoundation = $schoolId
            ? rescue(fn () => \App\Models\School::where('id', $schoolId)->whereNotNull('foundation_id')->exists(), false, false)
            : false;

        $groups = [];
        foreach ($cfg['groups'] as $group) {
            if (!empty($group['foundation_only']) && !$hasFoundation) {
                continue;
            }
            $allowed = in_array('*', $group['roles'] ?? ['*'], true) || in_array($role, $group['roles'] ?? [], true);
            if (!$allowed) {
                continue;
            }

            $items = [];
            foreach ($group['items'] as $item) {
                $itemRoles = $item['roles'] ?? $group['roles'];
                if (!in_array('*', $itemRoles, true) && !in_array($role, $itemRoles, true)) {
                    continue;
                }
                if (!RouteFacade::has($item['route'])) {
                    continue; // never render a dead menu
                }
                $items[] = $item + ['url' => route($item['route'])];
            }

            if (count($items) > 0) {
                $groups[] = [
                    'key' => $group['key'],
                    'label' => $group['label'],
                    'icon' => $group['icon'],
                    'items' => $items,
                ];
            }
        }

        return $groups;
    }

    /**
     * Top links (Dashboard / My Work / Calendar / Notifications).
     */
    public function topLinksFor($user): array
    {
        $role = $user?->getRoleNames()->first() ?? 'admin';
        $links = [];
        foreach (config('navigation.top') as $link) {
            $roles = $link['roles'] ?? ['*'];
            if (!in_array('*', $roles, true) && !in_array($role, $roles, true)) {
                continue;
            }
            if (!RouteFacade::has($link['route'])) {
                continue;
            }
            $links[] = $link + ['url' => route($link['route'])];
        }

        return $links;
    }

    /**
     * Flat list of every visible nav entry — feeds the command palette.
     */
    public function flatForPalette($user): array
    {
        $out = [];
        foreach ($this->topLinksFor($user) as $link) {
            $out[] = [
                'title' => $link['label'],
                'group' => 'Beranda',
                'icon' => $link['icon'] === 'work' ? 'edit' : ($link['icon'] === 'home' ? 'home' : $link['icon']),
                'url' => $link['url'],
            ];
        }
        foreach ($this->groupsFor($user) as $group) {
            foreach ($group['items'] as $item) {
                $out[] = [
                    'title' => $item['label'],
                    'group' => $group['label'],
                    'icon' => $group['icon'] === 'academic' ? 'school'
                        : ($group['icon'] === 'book-open' ? 'book'
                        : ($group['icon'] === 'office' ? 'edit'
                        : ($group['icon'] === 'chart' ? 'chart'
                        : ($group['icon'] === 'students' ? 'user'
                        : ($group['icon'] === 'people' ? 'users'
                        : ($group['icon'] === 'finance' ? 'money'
                        : ($group['icon'] === 'comm' ? 'bell'
                        : 'school'))))))),
                    'url' => $item['url'],
                ];
            }
        }

        return $out;
    }

    /**
     * Badge counts used by sidebar/topbar (cached, tenant-scoped, safe).
     */
    public function badges(int $schoolId): array
    {
        return Cache::remember("nav:badges:{$schoolId}", $this->badgeTtl, function () use ($schoolId) {
            return [
                'ppdb' => rescue(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)
                    ->whereIn('status', ['pending', 'submitted', 'new'])->count(), 0, false),
                'invoices' => rescue(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)
                    ->whereIn('status', ['unpaid', 'partial', 'overdue'])->count(), 0, false),
                'mywork' => rescue(fn () => app(\App\Services\Dashboard\MyWorkService::class)->totalCount($schoolId, auth()->user()), 0, false),
            ];
        });
    }

    /**
     * Breadcrumb group label for a route name (fallback via prefixes).
     */
    public function groupForRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        // Direct group membership check first.
        foreach (config('navigation.groups') as $group) {
            foreach ($group['items'] as $item) {
                foreach (explode('|', $item['active'] ?? '') as $pattern) {
                    $pattern = trim(str_replace('*', '', $pattern));
                    if ($pattern !== '' && str_starts_with($routeName, $pattern)) {
                        return $group['label'];
                    }
                }
            }
        }

        foreach (config('navigation.breadcrumb_prefixes') as $prefix => $label) {
            if (str_starts_with($routeName, $prefix)) {
                return $label;
            }
        }

        return null;
    }

    /**
     * Quick-create actions for the topbar "+ Buat" button.
     */
    public function quickCreateFor($user): array
    {
        $role = $user?->getRoleNames()->first();
        $sets = config('navigation.quick_create_roles')[$role] ?? null;
        if (!$sets) {
            return [];
        }

        $pool = config('navigation.quick_create');
        $items = [];
        foreach ((array) $sets as $setKey) {
            foreach ($pool[$setKey] ?? [] as $item) {
                if (RouteFacade::has($item['route'])) {
                    $items[] = $item + ['url' => route($item['route'])];
                }
            }
        }

        return collect($items)->unique('route')->values()->all();
    }

    /** Flush cached navigation data for a school. */
    public static function flushBadges(int $schoolId): void
    {
        Cache::forget("nav:badges:{$schoolId}");
    }
}
