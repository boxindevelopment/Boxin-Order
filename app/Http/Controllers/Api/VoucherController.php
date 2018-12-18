<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Repositories\Contracts\VoucherRepository;

class VoucherController extends Controller
{
    protected $repository;

    public function __construct(VoucherRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $voucher = $this->repository->all();

        if(count($voucher) > 0) {
            $data = VoucherResource::collection($voucher);

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
