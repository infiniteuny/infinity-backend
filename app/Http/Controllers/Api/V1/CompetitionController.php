<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UpdateCompetitionRequest;
use App\Models\Competition;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Competition::class, 'competition');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitions = QueryBuilder::for(Competition::class)
            ->allowedFilters([
                'name',
                'description',
                'url',
                'organizer',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'name',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('competitions', $competitions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionRequest $request): JsonResponse
    {
        $competition = Competition::create($request->validated());

        return ResponseFormatter::singleton('competition', $competition, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition): JsonResponse
    {
        return ResponseFormatter::singleton('competition', $competition);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionRequest $request, Competition $competition): JsonResponse
    {
        $competition->update($request->validated());

        return ResponseFormatter::singleton('competition', $competition);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition): JsonResponse
    {
        $competition->delete();

        return ResponseFormatter::singleton('competition', $competition);
    }
}
