<?php
namespace Gihyo\BookReservation\Model;

use Rhumsaa\Uuid\Uuid;

/**
 * Class Reservation
 * @package Gihyo\BookReservation\Model
 */
class Reservation extends AppModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->BelongsTo('Gihyo\BookReservation\Model\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function book()
    {
        return $this->BelongsTo('Gihyo\BookReservation\Model\Book');
    }

    /**
     * @return string
     */
    public function generateReservationCode()
    {
        return Uuid::uuid4()->toString();
    }
}