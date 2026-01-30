<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Colony;
use Illuminate\Http\Request;

class ColonyController extends Controller
{
    public function index(Request $request)
    {
        $query = Colony::query()
            ->where('is_active', true);

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $colonies = $query
            ->orderBy('city')
            ->orderBy('name')
            ->get()
            ->groupBy('city')
            ->map(fn ($items) => $items->pluck('name')->values());

        return response()->json($colonies);
    }
}
