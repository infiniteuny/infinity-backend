<?php

namespace App\Jobs;

use App\Data\Outline\Collection as OutlineCollection;
use App\Enums\OutlinePermission;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Services\OutlineService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class CreateDocsCollection implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    private const PAGE_LIMIT = 100;

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
                ->expireAfter(120),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(OutlineService $outlineService): void
    {
        $integration = GroupIntegration::where('group_id', $this->group->id)->first();
        $owningGroupId = $integration?->docs_group_id;

        if (is_null($owningGroupId)) {
            return;
        }

        $collection = $this->findOrCreateCollection($outlineService, $this->collectionName);
        $synced = $this->syncCollectionGroups($outlineService, $collection->id, $owningGroupId);

        DB::transaction(function () use ($collection, $owningGroupId, $synced) {
            $fresh = GroupIntegration::where('group_id', $this->group->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return;
            }

            $fresh->docs_collection_id = $collection->id;

            if (! $synced) {
                $fresh->docs_collection_last_synced_at = null;

                if ($fresh->docs_group_id === $owningGroupId) {
                    $fresh->docs_group_id = null;
                    $fresh->docs_group_last_synced_at = null;
                }
            } else {
                $fresh->docs_collection_last_synced_at = $fresh->docs_group_id === $owningGroupId ? now() : null;
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
            $this->readOnlyGroupIds,
            fn ($groupId) => is_string($groupId) && $groupId !== '' && $groupId !== $owningGroupId,
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
                if (is_string($matched->permission)) {
                    return $outlineService->updateCollection($matched->id, new OutlineCollection(
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
                }

                return $matched;
            }

            $offset += self::PAGE_LIMIT;
        } while ($collections->count() === self::PAGE_LIMIT);

        return $outlineService->createCollection(new OutlineCollection(
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
    }
}
