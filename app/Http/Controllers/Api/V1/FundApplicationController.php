<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Jobs\DeleteBlob;
use App\Models\FundApplication;
use App\Repositories\StorageRepository;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FundApplicationController extends Controller
{
    public function __construct(
        protected StorageRepository $storageRepository,
    ) {
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

        return ResponseFormatter::paginatedCollection('fund_applications', $fundApplications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundApplicationRequest $request): JsonResponse
    {
        $loaManifest = $this->storageRepository->store($request->file('letter_of_acceptance'), 'documents/fund-applications/letter-of-acceptances');
        $proposalManifest = $this->storageRepository->store($request->file('proposal'), 'documents/fund-applications/proposals');

        $fundApplication = FundApplication::create(
            array_replace($request->validated(), [
                'letter_of_acceptance' => $loaManifest,
                'proposal' => $proposalManifest,
            ])
        );

        return ResponseFormatter::singleton('fund_application', $fundApplication, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FundApplication $fundApplication): JsonResponse
    {
        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication): JsonResponse
    {
        $hasLoa = $request->has('letter_of_acceptance');
        $hasProposal = $request->has('proposal');

        if ($hasLoa) {
            $loaEncodedManifest = $fundApplication->getRawOriginal('letter_of_acceptance');

            dispatch(new DeleteBlob($loaEncodedManifest));

            $loaManifest = $this->storageRepository->store($request->file('letter_of_acceptance'), 'documents/fund-applications/letter-of-acceptances');
        }

        if ($hasProposal) {
            $proposalEncodedManifest = $fundApplication->getRawOriginal('proposal');

            dispatch(new DeleteBlob($proposalEncodedManifest));

            $proposalManifest = $this->storageRepository->store($request->file('proposal'), 'documents/fund-applications/proposals');
        }

        $fundApplication->update(
            $hasLoa || $hasProposal
                ? array_replace($request->validated(), [
                    ...($hasLoa ? ['letter_of_acceptance' => $loaManifest] : []),
                    ...($hasProposal ? ['proposal' => $proposalManifest] : []),
                ])
                : $request->validated()
        );

        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundApplication $fundApplication): JsonResponse
    {
        $loaEncodedManifest = $fundApplication->getRawOriginal('letter_of_acceptance');
        $proposalEncodedManifest = $fundApplication->getRawOriginal('proposal');

        dispatch(new DeleteBlob($loaEncodedManifest));
        dispatch(new DeleteBlob($proposalEncodedManifest));

        $fundApplication->delete();

        return ResponseFormatter::singleton('fund_application', $fundApplication);
    }
}
