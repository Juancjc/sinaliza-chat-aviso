<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarGroups' => fn () => $this->sidebarGroups($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return Collection<int, array{id: int, nome: string}>
     */
    private function sidebarGroups(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return collect();
        }

        $groups = $user->isAdmin()
            ? $user->gruposCriados()
            : $user->gruposParticipando();

        return $groups
            ->select('grupos.id', 'grupos.nome')
            ->orderBy('grupos.nome')
            ->get()
            ->map(fn ($group) => [
                'id' => $group->id,
                'nome' => $group->nome,
            ]);
    }
}
