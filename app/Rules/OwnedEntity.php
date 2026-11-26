<?php

namespace App\Rules;

use App\Models\Achievement;
use App\Models\CommunityGroupMember;
use App\Models\FundApplication;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Token;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserPermission;
use App\Models\UserPersona;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;

class OwnedEntity implements ValidationRule
{
    /**
     * @param  class-string<Model>  $entity
     */
    public function __construct(
        private readonly string $entity,
        private readonly ?string $userId,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! is_string($value) ||
            ! Str::isUuid($value) ||
            ! is_string($this->userId) ||
            ! Str::isUuid($this->userId)
        ) {
            $fail("The {$attribute} field must be owned by the user.");

            return;
        }

        $isOwned = match ($this->entity) {
            User::class => User::whereKey($value)
                ->where('id', $this->userId)
                ->exists(),
            Team::class => Team::whereKey($value)
                ->where(function ($query) {
                    $query->where('leader_id', $this->userId)
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery->where('users.id', $this->userId);
                        });
                })
                ->exists(),
            Achievement::class,
            FundApplication::class => $this->entity::whereKey($value)
                ->whereHas('team', function ($teamQuery) {
                    $teamQuery->where('leader_id', $this->userId)
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery->where('users.id', $this->userId);
                        });
                })
                ->exists(),
            TeamMember::class => TeamMember::whereKey($value)
                ->where(function ($query) {
                    $query->where('user_id', $this->userId)
                        ->orWhereHas('team', function ($teamQuery) {
                            $teamQuery->where('leader_id', $this->userId);
                        });
                })
                ->exists(),
            CommunityGroupMember::class,
            Token::class,
            UserGroup::class,
            UserPermission::class,
            UserPersona::class => $this->entity::whereKey($value)
                ->where('user_id', $this->userId)
                ->exists(),
            default => throw new InvalidArgumentException("Unsupported entity [{$this->entity}] for ownership validation."),
        };

        if (! $isOwned) {
            $fail("The {$attribute} field must be owned by the user.");
        }
    }
}
