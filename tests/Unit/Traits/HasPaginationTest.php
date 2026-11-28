<?php

namespace Tests\Unit\Traits;

use App\Traits\HasPagination;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class HasPaginationTest extends TestCase
{
    public function test_resolve_per_page_clamps_integers_to_1_through_500_and_defaults_invalid_inputs_to_10(): void
    {
        $helper = new class
        {
            use HasPagination;

            public function check(array $query): int
            {
                return $this->resolvePerPage(Request::create('/v1/users', 'GET', $query));
            }
        };

        $this->assertSame(10, $helper->check([]));
        $this->assertSame(10, $helper->check(['per_page' => '']));
        $this->assertSame(10, $helper->check(['per_page' => 'abc']));
        $this->assertSame(10, $helper->check(['per_page' => ['20']]));
        $this->assertSame(1, $helper->check(['per_page' => '0']));
        $this->assertSame(1, $helper->check(['per_page' => '-5']));
        $this->assertSame(25, $helper->check(['per_page' => '25']));
        $this->assertSame(500, $helper->check(['per_page' => '500']));
        $this->assertSame(500, $helper->check(['per_page' => '100000']));
    }
}
