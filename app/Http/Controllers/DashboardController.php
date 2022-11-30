<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Balance_model;
use App\Models\Balance_log_model;
use App\Models\Transactions_model;
use App\Models\Charges_model;
use App\Models\Settlements_model;

class DashboardController extends Controller
{
  public function __construct(){
    // dd(Auth::check());
  }
  // Dashboard - Analytics
  public function dashboardAnalytics(Request $request)
  {
    if(Auth::check()){

    }else{
        return redirect('login');
    }
    $pageConfigs = ['pageHeader' => false];
    $user_id = Auth::user()->id;
    $start_date = date('Y-m-01 00:00:00');
    $end_date = date('Y-m-d 23:59:59');
    if($request->start != NULL && $request->end != NULL){
        $start_date = $request->start." 00:00:00";
        $start_date = $request->end." 23:59:59";
    }
                    // Current Balance
    $data['current_balance'] = Balance_model::where('parties_id',1)
                                                ->where('keeper_id',1)
                                                ->where('currency_id',27)
                                                ->first()->balance;
                    // Payments / MDR In
    $payments = Transactions_model::where('transaction_type',1)
                                    ->where('status',1)
                                    ->where('merchant_id',$user_id)
                                    ->where('datetime','>=',$start_date)
                                    ->where('datetime','<=',$end_date)->get();
    $data['mdr_in'] = 0;
    $data['in_charges']= 0;
    foreach ($payments as $payment) {
        $data['mdr_in'] += $payment->amount;
        $data['in_charges'] += $payment->charges;
    }
                    // Payouts / MDR outs

    $total_payouts = Transactions_model::where('transaction_type',2)
                                            ->where('merchant_id',$user_id)
                                            ->where('datetime','>=',$start_date)
                                            ->where('datetime','<=',$end_date)->get();
    $failed_payouts = Transactions_model::where('transaction_type',2)
                                            ->where('status',2)
                                            ->where('merchant_id',$user_id)
                                            ->where('datetime','>=',$start_date)
                                            ->where('datetime','<=',$end_date)->get();
    $payouts = Transactions_model::where('transaction_type',2)
                                    ->where('status',1)
                                    ->where('merchant_id',$user_id)
                                    ->where('datetime','>=',$start_date)
                                    ->where('datetime','<=',$end_date)->get();
    $data['mdr_out'] = 0;
    $data['out_charges'] = 0;
    foreach ($payouts as $payout) {
        $data['mdr_out'] += $payout->amount;
        $data['out_charges'] += $payout->charges;
    }
                                // Rolling Reserve
    $data['rolling_reserve'] = 0;
    $all_reserve_charges = Charges_model::select('id')
                                            ->where('take_from',3)
                                            ->where('take_from_id',$user_id)
                                            ->where('transaction_charges_id',7)->get();
    $all_charges = [];
    foreach($all_reserve_charges as $charge){
        $all_charges[] = $charge->id;
    }
    $merchant_balance = Balance_model::select('id')->where('parties_id',1)
                                                        ->where('keeper_id',1)
                                                        ->where('currency_id',27)
                                                        ->where('type',1)->first();
    $all_logs = Balance_log_model::select('amount')
                                        ->where('balance_id',$merchant_balance->id)
                                        ->whereIn('charges_id',$all_charges)->get();
    foreach($all_logs as $log){
        $data['rolling_reserve'] += $all_logs->amount;
    }

                                // Settlements
    $settlements = Settlements_model::where('give_to',3)
                                        ->where('give_to_id',$user_id)
                                        ->whereIn('settlement_type',array(1,2))
                                        ->where('datetime','>=',$start_date)
                                        ->where('datetime','<=',$end_date)->get();
    $data['settle'] = 0 ;
    $data['settle_charges'] = 0 ;
    foreach ($settlements as $settlement ) {
        $data['settle'] += $settlement->amount;
        $data['settle_charges'] += $settlement->charges;
    }
                                // Other Charges
    $other_charges = Settlements_model::where('give_to',3)
                                            ->where('give_to_id',$user_id)
                                            ->where('settlement_type',4)
                                            ->where('datetime','>=',$start_date)
                                            ->where('datetime','<=',$end_date)->get();
    $charges = 0 ;
    foreach ($other_charges as $other_charge ) {
        $charges += $other_charge->charges;
    }

    return view('/content/dashboard/dashboard-analytics', ['pageConfigs' => $pageConfigs])->with($data);
  }

  // Dashboard - Ecommerce
  public function dashboardEcommerce()
  {
    $pageConfigs = ['pageHeader' => false];

    return view('/content/dashboard/dashboard-ecommerce', ['pageConfigs' => $pageConfigs]);
  }
}
