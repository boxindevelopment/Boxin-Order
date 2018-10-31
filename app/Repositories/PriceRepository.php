<?php

namespace App\Repositories;

use App\Model\Price;
use App\Repositories\Contracts\PriceRepository as PriceRepositoryInterface;
use DB;

class PriceRepository implements PriceRepositoryInterface
{
    protected $model;

    public function __construct(Price $model)
    {
        $this->model = $model;
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function all()
    {
        return $this->model->get();
    }

    public function getChooseProduct($types_of_box_room_id, $types_of_duration_id, $city_id)
    {
        $price =  Price::select('types_of_box_room.name', DB::raw('MIN(price) as min'), DB::raw('MAX(price) as max'), 'types_of_duration.alias')
            ->leftJoin('types_of_box_room', 'types_of_box_room.id', '=', 'prices.types_of_box_room_id')
            ->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id')
            ->where('prices.types_of_box_room_id', $types_of_box_room_id)
            ->where('prices.types_of_duration_id', $types_of_duration_id)
            ->groupBy('types_of_box_room.name')
            ->groupBy('types_of_duration.alias')
            ->where('city_id', $city_id)
            ->first();

        return $price;

    }

    public function getPriceCity($types_of_box_room_id, $types_of_size_id, $city_id)
    {
        $price =  Price::where('types_of_box_room_id', $types_of_box_room_id)
            ->where('types_of_size_id', $types_of_size_id)
            ->where('city_id', $city_id)
            ->get();

        return $price;

    }

    public function getPrice($types_of_box_room_id, $types_of_size_id, $types_of_duration_id)
    {
        $price =  Price::where('types_of_box_room_id', $types_of_box_room_id)
            ->where('types_of_size_id', $types_of_size_id)
            ->where('types_of_duration_id', $types_of_duration_id)
            ->first();

        return $price;

    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Price $price, $data)
    {
        return $price->update($data);
    }

    public function delete(Price $price)
    {
        return $price->delete();
    }
}
