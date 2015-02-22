<?php
namespace Gihyo\BookReservation\Controller;

use Gihyo\BookReservation\Filter\ApiAuthFilter;
use Gihyo\BookReservation\Model\Reservation;
use Gihyo\BookReservation\Model\User;
use Gihyo\BookReservation\Service\ReservationService;
use Gihyo\BookReservation\Validation\ReservationValidorBuilder;
use Input;

/**
 * Class ReservationController
 * @package Gihyo\BookReservation\Controller
 */
class ReservationController extends AppController
{
    use ApiControllerTrait;

    /**
     * @var ReservationService
     */
    protected $service;

    /**
     * @param ReservationService $service
     */
    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $reservations = $this->service->readAll($this->getUser());

        return $this->responseOk($reservations);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $validator = (new ReservationValidorBuilder())->create(Input::all());
        if ($validator->fails()) {
            return $this->responseValidationError($validator->messages());
        }

        $reservation = $this->service->book($this->getUser(), Input::all());

        return $this->responseCreated($reservation);
    }

    /**
     * @param string $reservationCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($reservationCode)
    {
        $validator = (new ReservationValidorBuilder())->create(Input::all());
        if ($validator->fails()) {
            return $this->responseValidationError($validator->messages());
        }

        $reservation = Reservation::where('reservation_code', $reservationCode)->first();
        if (empty($reservation)) {
            return $this->responseNotFound();
        }

        $this->service->update($reservation, $this->getUser(), Input::all());
        return $this->responseOk();
    }

    /**
     * @param string $reservationCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($reservationCode)
    {
        $reservation = Reservation::where('reservation_code', $reservationCode)->first();
        if (empty($reservation)) {
            return $this->responseNotFound();
        }

        $this->service->cancel($reservation, $this->getUser());
        return $this->responseOk();
    }

    /**
     * @return User|null
     */
    protected function getUser()
    {
        return app()[ApiAuthFilter::AUTHORIZED_USER];
    }
}