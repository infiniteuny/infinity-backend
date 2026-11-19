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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_team_type_id`, `competition_scale_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `letter_of_acceptance`, `proposal`, `status`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `team`, `team.members`, `competition_instance`, `competition_scale`. No-example
     * @queryParam filters[team_id] string Filter by exact `team_id`. No-example
     * @queryParam filters[competition_instance_id] string Filter by exact `competition_instance_id`. No-example
     * @queryParam filters[competition_team_type_id] string Filter by exact `competition_team_type_id`. No-example
     * @queryParam filters[competition_scale_id] string Filter by exact `competition_scale_id`. No-example
     * @queryParam filters[competition_branch] string Filter by partial `competition_branch`. No-example
     * @queryParam filters[competition_start_date] string Filter by `competition_start_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[competition_end_date] string Filter by `competition_end_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[status] string Filter by exact `status`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_team_type_id`, `competition_scale_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `status`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
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
                'team.members',
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
                    $query->where('users.id', $userId);
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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_team_type_id`, `competition_scale_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `letter_of_acceptance`, `proposal`, `status`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `team`, `team.members`, `competition_instance`, `competition_scale`. No-example
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
                'team.members',
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
