<?php

namespace App\Jobs;

use App\Data\Outline\Collection as OutlineCollection;
use App\Enums\OutlinePermission;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Services\OutlineService;
use App\Services\OutlineServiceImpl;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;

class UpdateDocsCollection implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    private const PAGE_LIMIT = 100;

    public bool $deleteWhenMissingModels = true;

    public int $uniqueFor = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Group $group,
        public string $collectionName,
        /** @var string[] */
        public array $readOnlyGroupIds = [],
    ) {}

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 5;

    /**
     * The number of seconds to wait before retrying an exception.
     */
    public int $backoff = 10;

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(15);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->group->id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("docs-collection:{$this->group->id}"))
                ->shared()
                ->releaseAfter(15)
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(OutlineService $outlineService): void
    {
        if ($outlineService instanceof OutlineServiceImpl && ! $outlineService->isConfigured()) {
            return;
        }

        $syncedAt = now();
        $group = Group::find($this->group->id);

        if (! $group) {
            return;
        }

        $this->group = $group;

        $integration = GroupIntegration::where('group_id', $group->id)->first();
        $owningGroupId = is_string($integration?->docs_group_id) ? trim($integration->docs_group_id) : '';

        if ($owningGroupId === '') {
            return;
        }

        $docsCollectionId = is_string($integration?->docs_collection_id) ? trim($integration->docs_collection_id) : '';

        if ($docsCollectionId === '') {
            CreateDocsCollection::dispatch($group, $this->collectionName, $this->readOnlyGroupIds);

            return;
        }

        try {
            $updated = $outlineService->updateCollection($docsCollectionId, new OutlineCollection(
                id: Optional::create(),
                name: $this->collectionName,
                description: Optional::create(),
                permission: null,
                icon: Optional::create(),
                color: Optional::create(),
                sharing: Optional::create(),
                url: Optional::create(),
                urlId: Optional::create(),
            ));
            $docsCollectionId = $this->requireRemoteId($updated->id, 'collection');
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            $recreatedCollection = $this->findOrCreateCollection($outlineService, $this->collectionName);
            $docsCollectionId = $this->requireRemoteId($recreatedCollection->id, 'collection');
        }

        $synced = $this->syncCollectionGroups($outlineService, $docsCollectionId, $owningGroupId);

        DB::transaction(function () use ($group, $docsCollectionId, $owningGroupId, $synced, $syncedAt) {
            $fresh = GroupIntegration::where('group_id', $group->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return;
            }

            $fresh->docs_collection_id = $docsCollectionId;

            if (! $synced) {
                $fresh->docs_collection_last_synced_at = null;

                if ($fresh->docs_group_id === $owningGroupId) {
                    $fresh->docs_group_id = null;
                    $fresh->docs_group_last_synced_at = null;
                }
            } else {
                $fresh->docs_collection_last_synced_at = $fresh->docs_group_id === $owningGroupId ? $syncedAt : null;
            }

            $fresh->save();
        });
    }

    private function syncCollectionGroups(
        OutlineService $outlineService,
        string $collectionId,
        string $owningGroupId,
    ): bool {
        try {
            $outlineService->addGroupToCollection($collectionId, $owningGroupId, OutlinePermission::READ_WRITE);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            return false;
        }

        $readOnlyGroupIds = array_values(array_unique(array_filter(
            array_map(fn ($id) => is_string($id) ? trim($id) : '', $this->readOnlyGroupIds),
            fn (string $groupId) => $groupId !== '' && $groupId !== $owningGroupId,
        )));

        foreach ($readOnlyGroupIds as $groupId) {
            $outlineService->addGroupToCollection($collectionId, $groupId, OutlinePermission::READ);
        }

        return true;
    }

    private function findOrCreateCollection(OutlineService $outlineService, string $name): OutlineCollection
    {
        $offset = 0;

        do {
            $collections = $outlineService->listCollections([
                'limit' => self::PAGE_LIMIT,
                'offset' => $offset,
            ]);

            $matched = $collections->first(
                fn (OutlineCollection $collection) => is_string($collection->name) && $collection->name === $name
            );

            if ($matched !== null) {
                $matchedId = $this->requireRemoteId($matched->id, 'collection');

                if (is_string($matched->permission)) {
                    $updated = $outlineService->updateCollection($matchedId, new OutlineCollection(
                        id: Optional::create(),
                        name: $name,
                        description: Optional::create(),
                        permission: null,
                        icon: Optional::create(),
                        color: Optional::create(),
                        sharing: Optional::create(),
                        url: Optional::create(),
                        urlId: Optional::create(),
                    ));

                    $this->requireRemoteId($updated->id, 'collection');

                    return $updated;
                }

                return $matched;
            }

            $offset += self::PAGE_LIMIT;
        } while ($collections->count() === self::PAGE_LIMIT);

        $created = $outlineService->createCollection(new OutlineCollection(
            id: Optional::create(),
            name: $name,
            description: Optional::create(),
            permission: null,
            icon: Optional::create(),
            color: Optional::create(),
            sharing: Optional::create(),
            url: Optional::create(),
            urlId: Optional::create(),
        ));

        $this->requireRemoteId($created->id, 'collection');

        return $created;
    }

    private function requireRemoteId(mixed $id, string $resource): string
    {
        if ($id instanceof Optional || ! is_scalar($id) || is_bool($id)) {
            throw new RuntimeException("Outline {$resource} response is missing a valid scalar id.");
        }

        $normalized = trim((string) $id);

        if ($normalized === '') {
            throw new RuntimeException("Outline {$resource} response returned an empty id.");
        }

        return $normalized;
    }
}
