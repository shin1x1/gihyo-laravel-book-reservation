<?php
namespace Gihyo\BookReservation\Model;

use DB;

/**
 * Class Book
 * @package Gihyo\BookReservation\Model
 */
class Book extends AppModel
{
    /**
     * @param $quantity
     * @return int
     */
    public function decrementInventory($quantity)
    {
        $columns = [
            'inventory' => DB::raw(sprintf('inventory - %d', $quantity)),
        ];

        return DB::table($this->getTable())
            ->where('id', $this->id)
            ->where('inventory', '>=', $quantity)
            ->update($columns);
    }

    /**
     * @param $quantity
     * @return int
     */
    public function incrementInventory($quantity)
    {
        $columns = [
            'inventory' => DB::raw(sprintf('inventory + %d', $quantity)),
        ];

        return DB::table($this->getTable())
            ->where('id', $this->id)
            ->update($columns);
    }
}
