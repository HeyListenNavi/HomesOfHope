<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Listar usuarios con filtros opcionales y paginación.
     * * Ejemplos de endpoints válidos:
     * 1. Sin parámetros (Trae la página 1 con configuración por defecto):
     * GET /api/users
     * * 2. Navegación de paginación (Ir a la página 2):
     * GET /api/users?page=2
     * * 3. Filtrar solo por nombre:
     * GET /api/users?name=Carlos
     * * 4. Filtrar solo por correo:
     * GET /api/users?email=gmail.com
     * * 5. Búsqueda combinada (Nombre O Correo):
     * GET /api/users?name=Carlos&email=admin
     * * 6. Filtrar por rol:
     * GET /api/users?role=admin
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'string'],
            'role' => ['sometimes', 'string'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $users = User::query()
            ->with('roles')
            // Si el request trae 'name', aplica este filtro
            ->when($validated['name'] ?? null, fn ($q, $name) => $q->where('name', 'like', "%{$name}%"))
            // Si el request trae 'email', aplica este OR WHERE
            ->when($validated['email'] ?? null, fn ($q, $email) => $q->orWhere('email', 'like', "%{$email}%"))
            // Si el request trae 'role', aplica el scope de Spatie
            ->when($validated['role'] ?? null, fn ($q, $role) => $q->role($role))
            ->paginate(15);

        return $users->through(fn (User $user) => [
            ...$user->toArray(),
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('roles');

        return [
            ...$user->toArray(),
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8',
        ]);

        $user->update($validated);

        return $user;
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }
}
