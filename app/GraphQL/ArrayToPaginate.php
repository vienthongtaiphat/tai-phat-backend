<?php
namespace App\GraphQL;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ArrayToPaginate
{
    public static function paginate($items, $perPage = null, $page = null, $options = [])
    {
        if (count($items)) {
            $perPage = $perPage === null ? count($items) : $perPage;
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $items instanceof Collection ? $items : Collection::make($items);
            return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        }

        return null;
    }
}
