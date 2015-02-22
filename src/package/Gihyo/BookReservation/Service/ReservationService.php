<?php
namespace Gihyo\BookReservation\Service;

use DB;
use Gihyo\BookReservation\Exception\PreconditionException;
use Gihyo\BookReservation\Model\Book;
use Gihyo\BookReservation\Model\Reservation;
use Gihyo\BookReservation\Model\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ReservationService
 * @package Gihyo\BookReservation\Service
 */
class ReservationService
{
    /**
     * @param User $user
     * @return Collection|Reservation[]
     */
    public function readAll(User $user)
    {
        return Reservation::with(['user', 'book'])
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param User $user
     * @param array $inputs
     * @return Reservation
     */
    public function book(User $user, array $inputs)
    {
        return $this->store(new Reservation(), $user, $inputs);
    }

    /**
     * @param Reservation $reservation
     * @param User $user
     * @param array $inputs
     * @throws PreconditionException
     */
    public function update(Reservation $reservation, User $user, array $inputs)
    {
        if ($user->id !== $reservation->user_id) {
            throw new PreconditionException('could_not_update');
        }

        DB::transaction(function () use ($user, $reservation, $inputs) {
            $reservation->book->incrementInventory($reservation->quantity);
            $this->store($reservation, $user, $inputs);
        });
    }

    /**
     * @param Reservation $reservation
     * @param User $user
     * @throws PreconditionException
     */
    public function cancel(Reservation $reservation, User $user)
    {
        if ($user->id !== $reservation->user_id) {
            throw new PreconditionException('could_not_cancel');
        }

        DB::transaction(function () use ($user, $reservation) {
            $reservation->book->incrementInventory($reservation->quantity);
            $reservation->delete();
        });
    }

    /**
     * @param Reservation $reservation
     * @param User $user
     * @param array $inputs
     * @return Reservation
     * @throws PreconditionException
     */
    protected function store(Reservation $reservation, User $user, array $inputs)
    {
        /** @var Book $book */
        $book = Book::where('asin', $inputs['asin'])->first();
        if (empty($book)) {
            throw new PreconditionException('book_not_found');
        }
        if ($book->inventory < $inputs['quantity']) {
            throw new PreconditionException('inventory_lacked');
        }

        DB::transaction(function () use ($user, $book, &$reservation, $inputs) {
            $affectedRows = $book->decrementInventory($inputs['quantity']);
            if ($affectedRows !== 1) {
                throw new PreconditionException('inventory_lacked');
            }

            $reservation->user_id = $user->id;
            $reservation->book_id = $book->id;
            $reservation->quantity = $inputs['quantity'];
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = $reservation->generateReservationCode();
            }
            $reservation->save();
        });

        return $reservation;
    }
}