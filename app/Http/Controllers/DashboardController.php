<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $query = $user->isAdmin()
            ? $user->gruposCriados()
            : $user->gruposParticipando();

        $grupos = $query
            ->with('admin:id,name')
            ->withCount(['participantes', 'mensagens'])
            ->latest('grupos.created_at')
            ->get();

        return Inertia::render('Dashboard', [
            'grupos' => $grupos,
        ]);
    }
}
