<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use App\Http\Resources\AddItemBoxResource;
use App\Http\Resources\HistoryOrderDetailBoxResource;
use Auth;
use Validator;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use App\Model\HistoryOrderDetailBox;
use DB;
use Carbon\Carbon;
use Exception;

class HistoryItemController extends Controller
{
    
  public function get_by_box($id)
  {
    $history = HistoryOrderDetailBox::where('order_detail_id', $id)->get();
    return response()->json([
      'status'  => true,
      'data'    => HistoryOrderDetailBoxResource::collection($history)
    ]);
  }



}
