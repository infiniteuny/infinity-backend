<?php

namespace Tests\Unit\Traits;

use App\Models\Degree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class HasBulkCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_many_inserts_models_and_returns_empty_collection_when_cancelled_by_event(): void
    {
        $persisted = Degree::createMany([
            ['code' => 'S1', 'name' => 'Sarjana'],
            ['code' => 'S2', 'name' => 'Magister'],
        ]);

        $this->assertCount(2, $persisted);
        $this->assertDatabaseHas('degrees', ['code' => 'S1']);
        $this->assertDatabaseHas('degrees', ['code' => 'S2']);

        Event::listen('eloquent.creating: '.Degree::class, static fn (): bool => false);
        try {
            $cancelled = Degree::createMany([
                ['code' => 'D3', 'name' => 'Diploma'],
            ]);

            $this->assertCount(0, $cancelled);
            $this->assertDatabaseMissing('degrees', ['code' => 'D3']);
        } finally {
            Event::forget('eloquent.creating: '.Degree::class);
        }
    }
}
