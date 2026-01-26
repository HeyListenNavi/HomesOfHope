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
     */
    public function index(Request $request)
    {
        $users = User::query()
            // Si el request trae 'name', aplica este filtro
            ->when($request->name, fn($q, $name) => $q->where('name', 'like', "%{$name}%"))
            // Si el request trae 'email', aplica este OR WHERE
            ->when($request->email, fn($q, $email) => $q->orWhere('email', 'like', "%{$email}%"))
            ->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
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
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}