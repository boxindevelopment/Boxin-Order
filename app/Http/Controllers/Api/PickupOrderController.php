<?php

namespace App\Http\Controllers\Api;

use App\Model\TypePickup;
use App\Model\Order;
use App\Model\PickupOrder;
use App\Model\Warehouse;
use App\Model\Setting;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypePickupResource;
use App\Http\Resources\PickupOrderResource;
use Illuminate\Http\Request;
use App\Repositories\Contracts\WarehouseRepository;

class PickupOrderController extends Controller
{
    protected $warehouse;

    public function __construct(WarehouseRepository $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getType()
    {

        $types = TypePickup::get();

        if(count($types) != 0) {
            $data = TypePickupResource::collection($types);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }

}
