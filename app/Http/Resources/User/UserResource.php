<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected $resourceName = 'user';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toBaseArray(Request $request): array
    {
        $user = $this->resource->toArray();

        if (! Auth::guard(config('auth.defaults.semi_public_guard'))->user()) {
            $classifiedFields = [
                'email_address',
                'phone_number',
                'student_id',
            ];

            foreach ($classifiedFields as $field) {
                if (isset($user[$field])) {
                    $user[$field] = 'REDACTED';
                }
            }
        }

        return $user;
    }
}
