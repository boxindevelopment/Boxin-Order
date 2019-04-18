<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepository;

class CategoryController extends Controller
{
    protected $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {

        $cat = $this->repository->all();
        if(count($cat) > 0) {
            $data = CategoryResource::collection($cat);
            return response()->json([
                'status' => true,
                'data' => $data,
                'database' => env('DB_DATABASE')
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);

    }

}
