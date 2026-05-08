<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Http\Resources\FundApplication\FundApplicationCollection;
use App\Http\Resources\FundApplication\FundApplicationResource;
use App\Jobs\DeleteBlob;
use App\Models\FundApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Fund Applications
 * Manage fund applications.
 */
class FundApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FundApplication::class, 'fund_application');
    }

    /**
     * List all fund applications
     *
     * @apiResourceCollection App\Http\Resources\FundApplication\FundApplicationCollection
     *
     * @apiResourceModel App\Models\FundApplication paginate=10,cursor
     */
    public function index(Request $request)
    {
        $user = Auth::guard()->user();
        $userId = $user->id;

        $fundApplications = QueryBuilder::for(FundApplication::class)
            ->allowedFields(
                'id',
                'team_id',
                'competition_instance_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'letter_of_acceptance',
                'proposal',
                'status',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'team',
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
            )
            ->allowedFilters(
                AllowedFilter::exact('team_id'),
                AllowedFilter::exact('competition_instance_id'),
                AllowedFilter::exact('competition_team_type_id'),
                AllowedFilter::exact('competition_scale_id'),
                'competition_branch',
                AllowedFilter::operator('competition_start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('competition_end_date', FilterOperator::DYNAMIC),
                AllowedFilter::exact('status'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'team_id',
                'competition_instance_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'status',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        if (! $user->can('read-fund-application')) {
            $fundApplications = $fundApplications
                ->whereHas('team', function ($query) use ($userId) {
                    $query->where('leader_id', $userId);
                })
                ->orWhereHas('team.members', function ($query) use ($userId) {
                    $query->where('team_members.id', $userId);
                });
        }

        $fundApplications = $fundApplications->cursorPaginate($request->query('per_page', 10));

        return new FundApplicationCollection($fundApplications);
    }

    /**
     * Create a fund application
     *
     * @apiResource App\Http\Resources\FundApplication\FundApplicationResource status=201
     *
     * @apiResourceModel App\Models\FundApplication
     */
    public function store(StoreFundApplicationRequest $request)
    {
        $loaManifest = Storage::store(
            $request->file('letter_of_acceptance'),
            'fund-applications/letter-of-acceptances',
        );
        $proposalManifest = Storage::store(
            $request->file('proposal'),
            'fund-applications/proposals',
        );

        $fundApplication = FundApplication::create(
            array_replace($request->validated(), [
                'letter_of_acceptance' => $loaManifest,
                'proposal' => $proposalManifest,
            ])
        );

        return new FundApplicationResource($fundApplication);
    }

    /**
     * Retrieve a fund application
     *
     * @apiResource App\Http\Resources\FundApplication\FundApplicationResource
     *
     * @apiResourceModel App\Models\FundApplication
     */
    public function show(FundApplication $fundApplication)
    {
        $fundApplication = QueryBuilder::for(FundApplication::where('id', $fundApplication->id))
            ->allowedFields(
                'id',
                'team_id',
                'competition_instance_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'letter_of_acceptance',
                'proposal',
                'status',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'team',
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
            )
            ->firstOrFail();

        return new FundApplicationResource($fundApplication);
    }

    /**
     * Update a fund application
     *
     * @apiResource App\Http\Resources\FundApplication\FundApplicationResource
     *
     * @apiResourceModel App\Models\FundApplication
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication)
    {
        $hasLoa = $request->has('letter_of_acceptance');
        $hasProposal = $request->has('proposal');

        if ($hasLoa) {
            $oldLoaManifest = $fundApplication->getRawOriginal('letter_of_acceptance');

            DeleteBlob::dispatch($oldLoaManifest);

            $loaManifest = Storage::store(
                $request->file('letter_of_acceptance'),
                'fund-applications/letter-of-acceptances',
            );
        }

        if ($hasProposal) {
            $oldProposalManifest = $fundApplication->getRawOriginal('proposal');

            DeleteBlob::dispatch($oldProposalManifest);

            $proposalManifest = Storage::store(
                $request->file('proposal'),
                'fund-applications/proposals',
            );
        }

        $fundApplication->update(
            $hasLoa || $hasProposal
                ? array_replace($request->validated(), [
                    ...($hasLoa ? ['letter_of_acceptance' => $loaManifest] : []),
                    ...($hasProposal ? ['proposal' => $proposalManifest] : []),
                ])
                : $request->validated()
        );

        return new FundApplicationResource($fundApplication);
    }

    /**
     * Delete a fund application
     *
     * @apiResource App\Http\Resources\FundApplication\FundApplicationResource
     *
     * @apiResourceModel App\Models\FundApplication
     */
    public function destroy(FundApplication $fundApplication)
    {
        $loaManifest = $fundApplication->getRawOriginal('letter_of_acceptance');
        $proposalManifest = $fundApplication->getRawOriginal('proposal');

        DeleteBlob::dispatch($loaManifest);
        DeleteBlob::dispatch($proposalManifest);

        $fundApplication->delete();

        return new FundApplicationResource($fundApplication);
    }
}
