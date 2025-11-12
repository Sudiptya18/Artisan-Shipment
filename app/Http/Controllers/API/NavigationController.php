<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NavigationResource;
use App\Models\Navigation;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function index(Request $request)
    {
        $navigations = Navigation::query()
            ->with([
                'children' => fn ($query) => $query->visible(),
            ])
            ->visible()
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        return NavigationResource::collection($navigations);
    }
}
