<?php

namespace App\Data\Authentik;

use App\Models\Group as GroupModel;
use RuntimeException;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Group extends Data
{
    public function __construct(
        #[MapName('pk')]
        public int|string|null|Optional $sso_id,
        #[MapName('parents')]
        public array|Optional $sso_parent_ids,
        #[MapName('users')]
        /** @var int[] */
        public array|Optional $user_ids,
        public string|Optional $name,
        public bool|Optional $is_superuser,
    ) {}

    public static function fromModel(GroupModel $group): self
    {
        $users = $group->exists
            ? $group->loadMissing('users')->users
            : ($group->relationLoaded('users') ? $group->users : collect());

        $userIds = $users
            ->pluck('sso_id')
            ->map(fn ($id) => is_scalar($id) ? trim((string) $id) : '')
            ->filter(fn (string $id) => $id !== '' && ctype_digit($id))
            ->map(fn (string $id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return new self(
            sso_id: Optional::create(),
            sso_parent_ids: Optional::create(),
            user_ids: $userIds,
            name: $group->name,
            is_superuser: Optional::create(),
        );
    }

    public function requireSsoId(): string
    {
        if ($this->sso_id instanceof Optional || ! is_scalar($this->sso_id) || is_bool($this->sso_id)) {
            throw new RuntimeException('Authentik group response is missing a valid scalar pk.');
        }

        $id = trim((string) $this->sso_id);

        if ($id === '') {
            throw new RuntimeException('Authentik group response returned an empty pk.');
        }

        return $id;
    }
}
