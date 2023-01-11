<?php

namespace Wyxos\LaravelListing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class WyxosListing
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function query();

    abstract public function filters(Builder|\Laravel\Scout\Builder $base);

    public function perPage()
    {
        return 10;
    }

    public function handle()
    {
        $page = $this->request->offsetGet('page') ?: 1;

        $base = $this->query();

        $this->filters($base);

        $pagination = $base->paginate($this->perPage(), ['*'], null, $page);

        $query = [
            'query' => [
                'items' => collect($pagination->items())->map(fn($item) => $this->format($item)),
                'total' => $pagination->total(),
                'perPage' => $this->perPage()
            ]
        ];

        return [
            ...$query,
            ...$this->data()
        ];
    }

    public function format($item)
    {
        return $item;
    }

    public function data(): array
    {
        return [];
    }

    public static function get()
    {
        /** @var WyxosListing $instance */
        $instance = app(static::class);

        return $instance->handle();
    }
}
