<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\DTOs\StoreSeasonData;
use Modules\CourseCatalogue\Models\Season;

class SeasonController extends Controller
{
    public function index(): JsonResponse
    {
        $seasons = Season::orderByDesc('start_date')->get();

        return response()->json(['data' => $seasons]);
    }

    public function show(Season $season): JsonResponse
    {
        return response()->json(['data' => $season]);
    }

    public function store(StoreSeasonData $data): JsonResponse
    {
        $season = Season::create($data->toArray());

        return response()->json(['data' => $season], 201);
    }

    public function update(Request $request, Season $season): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:course_catalogue.seasons,code,'.$season->id,
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'member_registration_start' => 'nullable|date',
            'public_registration_start' => 'nullable|date',
        ]);

        $season->update($validated);

        return response()->json(['data' => $season]);
    }

    public function destroy(Season $season): JsonResponse
    {
        $season->delete();

        return response()->json(['data' => ['message' => 'Season deleted.']]);
    }
}
