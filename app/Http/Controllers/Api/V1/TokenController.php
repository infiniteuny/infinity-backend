<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Token\TokenCollection;
use App\Http\Resources\Token\TokenResource;
use App\Models\Token;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Tokens
 * Manage tokens.
 */
class TokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny,'.Token::class)->only('index');
        $this->middleware('can:view,token')->only('show');
        $this->middleware('can:delete,token')->only('destroy');
    }

    /**
     * List all tokens
     *
     * @apiResourceCollection App\Http\Resources\Token\TokenCollection
     *
     * @apiResourceModel App\Models\Token
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $tokens = QueryBuilder::for(Token::class)
            ->allowedFields([
                'id',
                'user_id',
                'external_id',
                'last_used_at',
                'created_at',
                'expires_at',
            ])
            ->allowedIncludes([
                'user',
            ])
            ->allowedFilters([
                AllowedFilter::exact('user_id'),
                'external_id',
                AllowedFilter::operator('last_used_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'user_id',
                'external_id',
                'last_used_at',
                'created_at',
                'expires_at',
            ])
            ->defaultSorts([
                '-id',
            ]);

        if ($request->user()->can('read-tokens')) {
            $tokens = $tokens;
        } else {
            $tokens = $tokens->where('user_id', $userId);
        }

        $tokens = $tokens->cursorPaginate($request->query('per_page', 10));

        return new TokenCollection($tokens);
    }

    /**
     * Retrieve a token
     *
     * @apiResource App\Http\Resources\Token\TokenResource
     *
     * @apiResourceModel App\Models\Token
     */
    public function show(Token $token)
    {
        $token = QueryBuilder::for(Token::where('id', $token->id))
            ->allowedFields([
                'id',
                'user_id',
                'external_id',
                'last_used_at',
                'created_at',
                'expires_at',
            ])
            ->allowedIncludes([
                'user',
            ])
            ->firstOrFail();

        return new TokenResource($token);
    }

    /**
     * Delete a token
     *
     * @apiResource App\Http\Resources\Token\TokenResource
     *
     * @apiResourceModel App\Models\Token
     */
    public function destroy(Token $token)
    {
        $token->delete();

        return new TokenResource($token);
    }
}
