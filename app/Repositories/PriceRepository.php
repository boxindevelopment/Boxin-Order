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

    public function getChooseProduct($types_of_box_room_id, $types_of_duration_id, $area_id)
    {
        $min = Price::where('types_of_box_room_id', $types_of_box_room_id)
                    ->where('types_of_duration_id', 2)
                    ->where('area_id', $area_id)
                    ->min('price');
        $max = Price::where('prices.types_of_box_room_id', $types_of_box_room_id)
                    ->where('prices.types_of_duration_id', 3)
                    ->where('area_id', $area_id)
                    ->max('price');
        $query =  Price::query();
        $query->select('types_of_box_room.name', 'types_of_duration.alias');
        $query->leftJoin('types_of_box_room', 'types_of_box_room.id', '=', 'prices.types_of_box_room_id');
        $query->leftJoin('types_of_duration', 'types_of_duration.id', '=', 'prices.types_of_duration_id');
        $query->where('prices.types_of_box_room_id', $types_of_box_room_id);
        $query->where('prices.types_of_duration_id', $types_of_duration_id);
        $query->groupBy('types_of_box_room.name');
        $query->groupBy('types_of_duration.alias');
        $query->where('area_id', $area_id);
        $data = $query->first();

        if($data){
            $data = (object) ['name' => $data->name, 'min' => $min, 'max' => $max, 'type_of_box_room_id' => $data->type_of_box_room_id, 'alias' => $data->alias];
            return $data;
        } else {
            return [];
        }

    }

    public function getPriceArea($types_of_box_room_id, $types_of_size_id, $area_id)
    {
        $price =  Price::where('types_of_box_room_id', $types_of_box_room_id)
            ->where('types_of_size_id', $types_of_size_id)
            ->where('area_id', $area_id)
            ->get();

        return $price;

    }

    public function getPrice($types_of_box_room_id, $types_of_size_id, $types_of_duration_id, $area_id)
    {
        $price =  Price::where('types_of_box_room_id', $types_of_box_room_id)
            ->where('types_of_size_id', $types_of_size_id)
            ->where('types_of_duration_id', $types_of_duration_id)
            ->where('area_id', $area_id)
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
