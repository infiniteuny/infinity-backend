<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Models\FundApplication;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FundApplicationController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(FundApplication::class, 'fund_application');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $fundApplications = QueryBuilder::for(FundApplication::class)
            ->allowedFilters([
                'team_id',
                'competition_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_branch',
                'competition_date',
                'letter_of_acceptance',
                'proposal',
                'status',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'team_id',
                'competition_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_branch',
                'competition_date',
                'letter_of_acceptance',
                'proposal',
                'status',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('fund_applications', $fundApplications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundApplicationRequest $request)
    {
        $fundApplication = FundApplication::create($request->validated());

        return ResponseFormatter::singleton('fund_application', $fundApplication, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FundApplication $fundApplication)
    {
        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication)
    {
        $fundApplication->update($request->validated());

        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundApplication $fundApplication)
    {
        $fundApplication->delete();

        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }
}
