<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Token\TokenCollection;
use App\Http\Resources\Token\TokenResource;
use App\Models\Token;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Tokens
 * Manage tokens.
 */
class TokenController extends Controller
{
    use HasPagination;

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
     * @apiResourceModel App\Models\Token paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `user_id`, `sso_id`, `last_used_at`, `created_at`, `expires_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `user`. No-example
     * @queryParam filters[user_id] string Filter by exact `user_id`. No-example
     * @queryParam filters[sso_id] string Filter by partial `sso_id`. No-example
     * @queryParam filters[last_used_at] string Filter by `last_used_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[expires_at] string Filter by `expires_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `user_id`, `sso_id`, `last_used_at`, `created_at`, `expires_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $user = Auth::guard()->user();
        $userId = $user->id;
        $tokens = QueryBuilder::for(Token::class)
            ->allowedFields(
                'id',
                'user_id',
                'sso_id',
                'last_used_at',
                'created_at',
                'expires_at',
            )
            ->allowedIncludes('user')
            ->allowedFilters(
                AllowedFilter::exact('user_id'),
                'sso_id',
                AllowedFilter::operator('last_used_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('expires_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'user_id',
                'sso_id',
                'last_used_at',
                'created_at',
                'expires_at',
            )
            ->defaultSorts('-id');

        if (! $user->can('read-token')) {
            $tokens = $tokens->where('user_id', $userId);
        }

        $tokens = $tokens->cursorPaginate($this->resolvePerPage($request));

        return new TokenCollection($tokens);
    }

    /**
     * Retrieve a token
     *
     * @apiResource App\Http\Resources\Token\TokenResource
     *
     * @apiResourceModel App\Models\Token
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `user_id`, `sso_id`, `last_used_at`, `created_at`, `expires_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `user`. No-example
     */
    public function show(Token $token)
    {
        $token = QueryBuilder::for(Token::where('id', $token->id))
            ->allowedFields(
                'id',
                'user_id',
                'sso_id',
                'last_used_at',
                'created_at',
                'expires_at',
            )
            ->allowedIncludes('user')
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
