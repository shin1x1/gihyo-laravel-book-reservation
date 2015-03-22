<?php
use Gihyo\BookReservation\Filter\ApiAuthFilter;
use Gihyo\BookReservation\Model\Book;
use Gihyo\BookReservation\Model\Reservation;
use Gihyo\BookReservation\Model\User;
use Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiReservationTest
 */
class ApiReservationTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        $this->seed(__CLASS__ . 'Seeder');

        Route::enableFilters();
    }

    /**
     * @test
     */
    public function index()
    {
        $headers = [
            'HTTP_' . ApiAuthFilter::APPLICATION_TOKEN => 'token1',
        ];
        $this->client->request('GET', '/api/reservations', [], [], $headers);

        $this->assertResponseOk();

        $json = json_decode($this->client->getResponse()->getContent());
        $this->assertSame(2, count($json));

        $this->assertSame(1, $json[0]->id);
        $this->assertSame('氏名1', $json[0]->user->name);
        $this->assertSame('書籍1', $json[0]->book->title);

        $this->assertSame(2, $json[1]->id);
    }

    /**
     * @test
     */
    public function create()
    {
        $headers = [
            'HTTP_' . ApiAuthFilter::APPLICATION_TOKEN => 'token1',
        ];
        $parameters = [
            'asin' => 'asin1',
            'quantity' => 2,
        ];
        $this->client->request('POST', '/api/reservation', $parameters, [], $headers);

        $this->assertResponseStatus(Response::HTTP_CREATED);

        $json = json_decode($this->client->getResponse()->getContent());
        $reservation = Reservation::where('reservation_code', $json->reservation_code)->first();

        $this->assertSame(1, $reservation->user_id);
        $this->assertSame(1, $reservation->book_id);
        $this->assertSame(2, $reservation->quantity);
    }

    /**
     * @test
     */
    public function update()
    {
        $headers = [
            'HTTP_' . ApiAuthFilter::APPLICATION_TOKEN => 'token1',
        ];
        $parameters = [
            'asin' => 'asin1',
            'quantity' => 3,
        ];
        $this->client->request('PUT', '/api/reservation/code1', $parameters, [], $headers);

        $this->assertResponseOk();

        $book = Book::where('asin', 'asin1')->first();
        $this->assertSame(8, $book->inventory);

        $reservation = Reservation::where('reservation_code', 'code1')->first();
        $this->assertSame(3, $reservation->quantity);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function update_invalid_reservation_code()
    {
        $headers = [
            'HTTP_' . ApiAuthFilter::APPLICATION_TOKEN => 'token1',
        ];
        $parameters = [
            'asin' => 'asin1',
            'quantity' => 3,
        ];
        $this->client->request('PUT', '/api/reservation/予約コード', $parameters, [], $headers);
    }

    /**
     * @test
     */
    public function delete()
    {
        $headers = [
            'HTTP_' . ApiAuthFilter::APPLICATION_TOKEN => 'token1',
        ];
        $this->client->request('DELETE', '/api/reservation/code1', [], [], $headers);

        $this->assertResponseOk();

        $book = Book::where('asin', 'asin1')->first();
        $this->assertSame(11, $book->inventory);

        $this->assertSame(2, Reservation::count());
        $this->assertFalse(Reservation::where('reservation_code', 'code1')->exists());
    }
}

/**
 * Class ApiReservationTestSeeder
 */
class ApiReservationTestSeeder extends Seeder
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
            ],
        ]);

        DB::table('books')->insert([
            [
                'id' => 1,
                'asin' => 'asin1',
                'title' => '書籍1',
                'price' => 1000,
                'inventory' => 10,
            ],
        ]);

        DB::table('reservations')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'book_id' => 1,
                'quantity' => 1,
                'reservation_code' => 'code1',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'book_id' => 1,
                'quantity' => 2,
                'reservation_code' => 'code2',
            ],
            [
                'id' => 3,
                'user_id' => 2,
                'book_id' => 1,
                'quantity' => 1,
                'reservation_code' => 'code3',
            ]
        ]);
    }
}
