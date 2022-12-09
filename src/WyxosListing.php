<?php

namespace Wyxos\LaravelListing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

abstract class WyxosListing
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function query();

    abstract public function filters(Builder $base);

    public function perPage()
    {
        return 10;
    }

    public function handle()
    {
        $page = $this->request->offsetGet('page') ?: 1;

        $base = $this->query();

        $this->filters($base);

        $pagination = $base->latest()->paginate($this->perPage(), ['*'], null, $page);

        $query = [
            'query' => [
                'items' => $this->format($pagination->items()),
                'total' => $pagination->total(),
                'perPage' => $this->perPage()
            ]
        ];

        return [
            ...$query,
            ...$this->data()
        ];
    }

    public function format(Collection $items): Collection
    {
        return $items;
    }

    protected function merge($query, $data)
    {
        return [

        ]
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
