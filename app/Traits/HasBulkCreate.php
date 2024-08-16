<?php

// Taken from https://github.com/n1215/eloquent-bulk-save

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LogicException;

trait HasBulkCreate
{
    /**
     * Create many models
     */
    public static function createMany(array $attributes): Collection
    {
        if (! is_subclass_of(static::class, Model::class)) {
            throw new LogicException(
                'Class using HasBulkCreate trait should be a subclass of '.Model::class
            );
        }

        $models = collect($attributes)
            ->map(function (array $attribute) {
                return (new static)->newModelInstance($attribute);
            });

        (new static)->saveMany($models);

        return $models;
    }

    /**
     * Save many models
     */
    private function saveMany(Collection $models): bool
    {
        if (! is_subclass_of(static::class, Model::class)) {
            throw new LogicException(
                'Class using HasBulkCreate trait should be a subclass of '.Model::class
            );
        }

        // Check the argument
        foreach ($models as $model) {
            if (! $model instanceof static) {
                throw new LogicException(static::class.'::saveMany() cannot be used for '.\get_class($model));
            }

            if ($model->exists) {
                throw new LogicException(
                    'This instance has already been persisted (class '.static::class.', primary key '.$model->getKey().')'
                );
            }
        }

        // Before save
        foreach ($models as $model) {
            if ($model->fireModelEvent('saving') === false) {
                return false;
            }
        }

        if ($models->isEmpty()) {
            return true;
        }

        // Before insert
        foreach ($models as $model) {
            if ($model->usesUniqueIds()) {
                $model->setUniqueIds();
            }

            if ($model->fireModelEvent('creating') === false) {
                return false;
            }

            if ($model->usesTimestamps()) {
                $model->updateTimestamps();
            }
        }

        // Perform insert
        $query = (new static)->newModelQuery();

        $attributes = (new static)->getAttributesForInsertMany($models);

        $query->insert($attributes);

        // After insert
        foreach ($models as $model) {
            $model->exists = true;
            $model->wasRecentlyCreated = true;
            $model->fireModelEvent('created', false);
        }

        // After save
        foreach ($models as $model) {
            $model->finishSave([]);
        }

        return true;
    }

    /**
     * Create array of model attributes
     */
    private function getAttributesForInsertMany(Collection $models): array
    {
        $attributesCollection = $models
            ->map(function (Model $model) {
                return $model->attributes;
            });

        $columns = $attributesCollection
            ->flatMap(function (array $attributesArray) {
                return array_keys($attributesArray);
            })
            ->unique()
            ->values();

        // Fill non-existent columns
        return $attributesCollection
            ->map(function (array $attributes) use ($columns) {
                foreach ($columns as $column) {
                    if (! array_key_exists($column, $attributes)) {
                        $attributes[$column] = null;
                    }
                }

                return $attributes;
            })
            ->toArray();
    }
}
