<?php
namespace Gihyo\BookReservation\Test\Service;

use DB;
use Gihyo\BookReservation\Exception\PreconditionException;
use Gihyo\BookReservation\Model\Book;
use Gihyo\BookReservation\Model\Reservation;
use Gihyo\BookReservation\Model\User;
use Gihyo\BookReservation\Service\ReservationService;
use Illuminate\Database\Seeder;
use TestCase;

/**
 * Class ReservationServiceTest
 * @package Gihyo\BookReservation\Test\Service
 */
class ReservationServiceTest extends TestCase
{
    /**
     * @var ReservationService
     */
    protected $sut;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->sut = new ReservationService();
        $this->migrateDatabase();
        $this->seed(__CLASS__ . 'Seeder');
    }

    /**
     * @test
     */
    public function readReservations()
    {
        $user = User::find(2);
        $actual = $this->sut->readAll($user);

        $this->assertSame(2, $actual->count());

        $this->assertSame(1, $actual[0]->id);
        $this->assertSame(2, $actual[1]->id);
    }

    /**
     * @test
     */
    public function readReservations_no_reservation()
    {
        $user = User::find(1);
        $actual = $this->sut->readAll($user);

        $this->assertSame(0, $actual->count());
    }

    /**
     * @test
     */
    public function book()
    {
        $user = User::find(1);
        $inputs = [
            'asin' => 'asin1',
            'quantity' => 2,
        ];
        $reservation = $this->sut->book($user, $inputs);

        $actual = Reservation::find($reservation->id);
        $this->assertSame(1, $actual->user_id);
        $this->assertSame(1, $actual->book_id);
        $this->assertSame(2, $actual->quantity);
        $this->assertRegExp('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/',
            $actual->reservation_code);

        $actual = Book::find(1);
        $this->assertSame(8, $actual->inventory);
    }

    /**
     * @test
     */
    public function book_not_found()
    {
        $user = User::find(1);
        $inputs = [
            'asin' => 'asin_not_found',
            'quantity' => 1,
        ];

        try {
            $this->sut->book($user, $inputs);
            $this->fail();
        } catch (PreconditionException $e) {
            $this->assertSame('book_not_found', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function book_inventory_lacked()
    {
        $user = User::find(1);
        $inputs = [
            'asin' => 'asin2',
            'quantity' => 4,
        ];

        try {
            $this->sut->book($user, $inputs);
            $this->fail();
        } catch (PreconditionException $e) {
            $this->assertSame('inventory_lacked', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function update()
    {
        $reservation = Reservation::find(1);
        $user = User::find(2);
        $inputs = [
            'asin' => 'asin1',
            'quantity' => 3,
        ];
        $this->sut->update($reservation, $user, $inputs);

        $actual = Reservation::find(1);
        $this->assertSame(2, $actual->user_id);
        $this->assertSame(1, $actual->book_id);
        $this->assertSame(3, $actual->quantity);
        $this->assertSame('code1', $actual->reservation_code);

        $actual = Book::find(2);
        $this->assertSame(4, $actual->inventory);

        $actual = Book::find(1);
        $this->assertSame(7, $actual->inventory);
    }

    /**
     * @test
     */
    public function cancel()
    {
        $reservation = Reservation::find(1);
        $user = User::find(2);
        $this->sut->cancel($reservation, $user);

        $this->assertFalse(Reservation::where('id', 1)->exists(1));

        $actual = Book::find(2);
        $this->assertSame(4, $actual->inventory);
    }
}

/**
 * Class ReservationServiceTestSeeder
 * @package Gihyo\BookReservation\Test\Service
 */
class ReservationServiceTestSeeder extends Seeder
{
    /**
     *
     */
    public function run()
    {
        Reservation::truncateWithIgnoreForeignKeyChecks();
        Book::truncateWithIgnoreForeignKeyChecks();
        User::truncateWithIgnoreForeignKeyChecks();

        DB::table('users')->insert([
            [
                'id' => 1,
                'api_token' => 'token1',
                'name' => '氏名1',
            ],
            [
                'id' => 2,
                'api_token' => 'token2',
                'name' => '氏名2',
            ]

        ]);

        DB::table('books')->insert([
            [
                'id' => 1,
                'asin' => 'asin1',
                'title' => '書籍1',
                'price' => 1000,
                'inventory' => 10,
            ],
            [
                'id' => 2,
                'asin' => 'asin2',
                'title' => '書籍2',
                'price' => 500,
                'inventory' => 3,
            ]
        ]);

        DB::table('reservations')->insert([
            [
                'id' => 1,
                'user_id' => 2,
                'book_id' => 2,
                'quantity' => 1,
                'reservation_code' => 'code1',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'book_id' => 1,
                'quantity' => 1,
                'reservation_code' => 'code2',
            ]
        ]);
    }
}