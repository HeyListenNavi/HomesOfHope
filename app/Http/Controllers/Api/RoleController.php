<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Get a list of all system roles.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
        ]);

        $roles = Role::query()
            ->when($validated['name'] ?? null, fn ($q, $name) => $q->where('name', 'like', "%{$name}%"))
            ->get();

        return $roles;
    }
}
