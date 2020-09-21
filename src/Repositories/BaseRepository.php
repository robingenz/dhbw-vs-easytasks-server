<?php
namespace App\Repositories;

abstract class BaseRepository
{
    private $table = '';

    public function __construct($table)
    {
        $this->table = $table;
    }

    protected function getTable()
    {
        return $this->table;
    }
}
