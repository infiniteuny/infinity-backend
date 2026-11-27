<?php

namespace App\Jobs;

use App\Data\Outline\Collection as OutlineCollection;
use App\Enums\OutlinePermission;
use App\Models\Group;
use App\Services\OutlineService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\LaravelData\Optional;

class UpdateDocsCollection implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

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
            new WithoutOverlapping($this->group->id),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(OutlineService $outlineService): void
    {
        $this->group->loadMissing('integration');

        $integration = $this->group->integration;

        if (is_null($integration?->docs_group_id)) {
            return;
        }

        if (is_null($integration->docs_collection_id)) {
            CreateDocsCollection::dispatch($this->group, $this->collectionName, $this->readOnlyGroupIds);

            return;
        }

        $docsCollectionId = $integration->docs_collection_id;

        try {
            $outlineService->updateCollection($docsCollectionId, new OutlineCollection(
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
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 404) {
                throw $exception;
            }

            $recreatedCollection = $this->findOrCreateCollection($outlineService, $this->collectionName);
            $docsCollectionId = $recreatedCollection->id;
            $integration->docs_collection_id = $docsCollectionId;
        }

        if (! $this->syncCollectionGroups($outlineService, $docsCollectionId, $integration->docs_group_id)) {
            $integration->docs_group_id = null;
            $integration->docs_group_last_synced_at = null;
            $integration->docs_collection_last_synced_at = null;
            $integration->save();

            return;
        }

        $integration->docs_collection_last_synced_at = now();
        $integration->save();
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
