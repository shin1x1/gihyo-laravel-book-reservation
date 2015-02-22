<?php
namespace Gihyo\BookReservation\Model;

use Eloquent;
use DB;

class AppModel extends Eloquent
{
    public static function truncateWithIgnoreForeignKeyChecks()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table((new static)->getTable())->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}