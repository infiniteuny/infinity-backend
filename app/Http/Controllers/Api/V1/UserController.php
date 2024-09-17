<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('users', $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return ResponseFormatter::singleton('user', $user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return ResponseFormatter::singleton('user', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return ResponseFormatter::singleton('user', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return ResponseFormatter::singleton('user', $user);
    }
}
