<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamMember\StoreCoreTeamMemberRequest;
use App\Http\Requests\CoreTeamMember\UpdateCoreTeamMemberRequest;
use App\Jobs\DeleteBlob;
use App\Models\CoreTeamMember;
use App\Repositories\StorageRepository;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CoreTeamMemberController extends Controller
{
    public function __construct(
        protected StorageRepository $storageRepository,
    ) {
        // $this->authorizeResource(Degree::class, 'Degree');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $coreTeamMembers = QueryBuilder::for(CoreTeamMember::class)
            ->allowedFilters([
                'code',
                'name',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('core_team_members', $coreTeamMembers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoreTeamMemberRequest $request): JsonResponse
    {
        $photoManifest = $this->storageRepository->store($request->file('photo'), 'images/core-team-members/photos');

        $hasAnimation = $request->has('animation');

        if ($hasAnimation) {
            $animationManifest = $this->storageRepository->store($request->file('animation'), 'images/core-team-members/animations');
        }

        $coreTeamMember = CoreTeamMember::create(
            array_replace($request->validated(), [
                'photo' => $photoManifest,
                ...($hasAnimation ? ['animation' => $animationManifest] : []),
            ])
        );

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeamMember $coreTeamMember): JsonResponse
    {
        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamMemberRequest $request, CoreTeamMember $coreTeamMember): JsonResponse
    {
        $hasPhoto = $request->has('image');
        $hasAnimation = $request->has('animation');
        $hasFileAnimation = $request->hasFile('animation');

        if ($hasPhoto) {
            $photoEncodedManifest = $coreTeamMember->getRawOriginal('photo');

            dispatch(new DeleteBlob($photoEncodedManifest));

            $photoManifest = $this->storageRepository->store($request->file('photo'), 'images/core-team-members/photos');
        }

        if ($hasAnimation) {
            $animationEncodedManifest = $coreTeamMember->getRawOriginal('animation');

            dispatch(new DeleteBlob($animationEncodedManifest));

            if ($hasFileAnimation) {
                $animationManifest = $this->storageRepository->store($request->file('animation'), 'images/core-team-members/animations');
            } else {
                $animationManifest = null;
            }
        }

        $coreTeamMember->update(
            $hasPhoto || $hasAnimation
                ? array_replace($request->validated(), [
                    ...($hasPhoto ? ['photo' => $photoManifest] : []),
                    ...($hasAnimation ? ['animation' => $animationManifest] : []),
                ])
                : $request->validated()
        );

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeamMember $coreTeamMember): JsonResponse
    {
        $photoEncodedManifest = $coreTeamMember->getRawOriginal('photo');
        $animationEncodedManifest = $coreTeamMember->getRawOriginal('animation');

        dispatch(new DeleteBlob($photoEncodedManifest));
        dispatch(new DeleteBlob($animationEncodedManifest));

        $coreTeamMember->delete();

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }
}
