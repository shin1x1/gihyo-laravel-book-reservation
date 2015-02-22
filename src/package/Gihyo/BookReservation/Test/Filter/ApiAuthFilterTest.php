<?php
namespace Gihyo\BookReservaion\Test\Filter;

use DB;
use Gihyo\BookReservation\Filter\ApiAuthFilter;
use Gihyo\BookReservation\Model\User;
use Illuminate\Database\Seeder;
use Request;
use TestCase;

/**
 * Class ApiAuthFilterTest
 * @package Gihyo\BookReservaion\Test\Filter
 */
class ApiAuthFilterTest extends TestCase
{
    /**
     * @var ApiAuthFilter
     */
    protected $sut;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->sut = new ApiAuthFilter();
        $this->migrateDatabase();
        $this->seed(__CLASS__ . 'Seeder');
    }

    /**
     * filter
     */
    public function testFilter()
    {
        Request::shouldReceive('header')->with(ApiAuthFilter::APPLICATION_TOKEN)->andReturn('token1');
        $actual = $this->sut->filter();
        $this->assertNull($actual);

        $user = app(ApiAuthFilter::AUTHORIZED_USER);
        $this->assertSame('氏名1', $user->name);
    }

    public function testFilter_mismatch_token()
    {
        Request::shouldReceive('header')->with(ApiAuthFilter::APPLICATION_TOKEN)->andReturn('token2');
        $actual = $this->sut->filter();
        $this->assertSame(401, $actual->getStatusCode());
    }

    public function testFilter_no_token()
    {
        $actual = $this->sut->filter();
        $this->assertSame(401, $actual->getStatusCode());
    }
}

/**
 * Class ApiAuthFilterTestSeeder
 * @package Gihyo\BookReservaion\Test\Filter
 */
class ApiAuthFilterTestSeeder extends Seeder
{
    /**
     *
     */
    public function run()
    {
        User::truncateWithIgnoreForeignKeyChecks();

        DB::table('users')->insert(
            [
                'id' => 1,
                'api_token' => 'token1',
                'name' => '氏名1',
            ]
        );
    }
}