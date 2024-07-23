<?php

namespace App\Http\Controllers;

use App\User;
use App\UserPlan;
use App\Transaction;
use App\Plan;
use App\BusRuleRef;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe;
use Session;
use Exception;
use WebhookEndpoint;
use StripePayment;
use Log;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = User::where('id', $request->user_id)->where('status', 'active')->first();
        if ($user) {
            $planData = Plan::where('status', 'active')->first();
            if ($planData) {
                return view('frontend.subscription.create', compact('planData', 'user'));
            }else{
                return back()->with('success','Plan not activate yet.');
            }
        }else{
            return redirect()->route('stripe_failed')->with('success','Subscription is faild.');
            abort(response()->json(
            [
                'status' => 403,
                'message' => 'UnAuthenticated',
                'data' => null
            ], 401));
        }
        
        
    }
    public function orderPost(Request $request)
    {
        $api_error = '';
        $user = User::where('id', $request->user_id)->first();
        if ($user) {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $booking_number = $this->generateRandomString(6);
            $input = $request->all();
            $token =  $request->stripeToken;
            $currency = 'usd';
            $planData = Plan::where('status', 'active')->first();
            if (!empty($planData)) {
                $planName = $planData->title; 
                $planPrice = $planData->amount; 
                $planInterval = 'month';
                $planPriceCents = round($planPrice*100);
                try {
                    $customer = \Stripe\Customer::create([ 
                        'name' => ucfirst($user->name),  
                        'id' => ucfirst($user->id)
                    ]);
                    if(empty($api_error) && $customer){ 
                        try { 
                            $price = \Stripe\Price::create([ 
                                'unit_amount' => $planPriceCents, 
                                'currency' => 'usd', 
                                'recurring' => ['interval' => $planInterval], 
                                'product_data' => ['name' => $planData->title],
                                
                            ]); 
                        } catch (Exception $e) { 
                            $api_error = $e->getMessage(); 
                        } 
                        if(empty($api_error) && $price){ 
                            // Create a new subscription 
                            try { 
                                if ($user->subscription_id != '') {
                                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                                    $stripe->subscriptions->cancel(
                                      $user->subscription_id,
                                      []
                                    );
                                }
                                $subscription = \Stripe\Subscription::create([ 
                                    'customer' => $customer->id, 
                                    'items' => [[ 
                                        'price' => $price->id, 
                                    ]],

                                      'metadata' => [

                                        'start_date' => date('Y-m-d', strtotime(date('Y-m-d'). ' + 30 days'))

                                      ], 
                                    'payment_behavior' => 'default_incomplete', 
                                    'expand' => ['latest_invoice.payment_intent'], 
                                ]); 
                            }catch(Exception $e) { 
                                $api_error = $e->getMessage(); 
                            } 
                            if(empty($api_error) && $subscription){
                                $user->subscription_id = $subscription->id;
                                $user->save();
                                $output = [ 
                                    'subscriptionId' => $subscription->id, 
                                    'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret, 
                                    'customerId' => $customer->id 
                                ]; 
                                $crr =  Stripe\Charge::create ([
                                        "amount" => $planPriceCents,
                                        "currency" => strtolower($currency),
                                        "source" => $request->stripeToken,
                                        "description" => "Transaction ID : ". $booking_number,
                                ]);
                                if($crr->status=="succeeded"){
                                    $txn_id = $crr->balance_transaction;
                                    $payment_method = 'stripe';
                                    if ($plan = Plan::where("id", $planData->id)->first()) {
                                        $days = $plan->duration_month;
                                        $user->plan_id = $planData->id;
                                        $user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));  
                                        if ($user->save()) {
                                            $array_old = array('status' => 'old');
                                            UserPlan::where('user_id', $user->id)->update($array_old);
                                            $user_plan = new UserPlan();
                                            $user_plan->user_id = $user->id;
                                            $user_plan->plan_id = $planData->id;
                                            $user_plan->title = $planData->title;
                                            $user_plan->amount = $planData->amount;
                                            $user_plan->duration_text = $planData->duration_text;
                                            $user_plan->duration_month = $planData->duration_month;
                                            $user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));
                                            if ($user_plan->save()) {
                                                $transaction=new Transaction();
                                                $transaction->user_id = $user->id;
                                                $transaction->status = 'active';
                                                $transaction->transaction_type = 'plan';
                                                $transaction->item_id = $planData->id;
                                                $transaction->txn_id = $txn_id;
                                                $transaction->payment_method = $payment_method;
                                                $transaction->before_wallet_amount = '0.00';
                                                $transaction->after_wallet_amount = '0.00';
                                                $transaction->amount = $planData->amount;
                                                $transaction->title = 'Plan Upgrade';
                                                $transaction->message = 'Plan Upgrade +'.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$planData->amount;
                                                $transaction->save();
                                            }
                                        }
                                    }
                                    $receipt_url = $crr->receipt_url;
                                    Session::flash('success', 'Payment Successful !');
                                    return view('frontend.subscription.stripe_success',compact('receipt_url'));
                                }else{
                                    return redirect()->route('stripe_failed')->with('success','Subscription is faild.');
                                }
                                //echo json_encode($output); 
                            }else{ 
                                return back()->with('success',$api_error);
                            } 
                        }else{ 
                            return back()->with('success',$api_error);
                        } 
                    }else{ 
                        return back()->with('success',$api_error); 

                    } 
                } catch (Exception $e) {
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $customer = $stripe->customers->retrieve(
                      $user->id,
                      []
                    );
                    if(empty($api_error) && $customer){ 
                        try { 
                            $price = \Stripe\Price::create([ 
                                'unit_amount' => $planPriceCents, 
                                'currency' => 'usd', 
                                'recurring' => ['interval' => $planInterval], 
                                'product_data' => ['name' => $planData->title],
                                
                            ]); 
                        } catch (Exception $e) { 
                            $api_error = $e->getMessage(); 
                        } 
                        if(empty($api_error) && $price){ 
                            // Create a new subscription 
                            try { 
                                if ($user->subscription_id != '') {
                                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                                    $stripe->subscriptions->cancel(
                                      $user->subscription_id,
                                      []
                                    );
                                }
                                $subscription = \Stripe\Subscription::create([ 
                                    'customer' => $customer->id, 
                                    'items' => [[ 
                                        'price' => $price->id, 
                                    ]],

                                      'metadata' => [

                                        'start_date' => date('Y-m-d', strtotime(date('Y-m-d'). ' + 30 days'))

                                      ], 
                                    'payment_behavior' => 'default_incomplete', 
                                    'expand' => ['latest_invoice.payment_intent'], 
                                ]); 
                            }catch(Exception $e) { 
                                $api_error = $e->getMessage(); 
                            } 
                            if(empty($api_error) && $subscription){
                                $user->subscription_id = $subscription->id;
                                $user->save();
                                $output = [ 
                                    'subscriptionId' => $subscription->id, 
                                    'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret, 
                                    'customerId' => $customer->id 
                                ]; 
                                $crr =  Stripe\Charge::create ([
                                        "amount" => $planPriceCents,
                                        "currency" => strtolower($currency),
                                        "source" => $request->stripeToken,
                                        "description" => "Transaction ID : ". $booking_number,
                                ]);
                                if($crr->status=="succeeded"){
                                    $txn_id = $crr->balance_transaction;
                                    $payment_method = 'stripe';
                                    if ($plan = Plan::where("id", $planData->id)->first()) {
                                        $days = $plan->duration_month;
                                        $user->plan_id = $planData->id;
                                        $user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));  
                                        if ($user->save()) {
                                            $array_old = array('status' => 'old');
                                            UserPlan::where('user_id', $user->id)->update($array_old);
                                            $user_plan = new UserPlan();
                                            $user_plan->user_id = $user->id;
                                            $user_plan->plan_id = $planData->id;
                                            $user_plan->title = $planData->title;
                                            $user_plan->amount = $planData->amount;
                                            $user_plan->duration_text = $planData->duration_text;
                                            $user_plan->duration_month = $planData->duration_month;
                                            $user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));
                                            if ($user_plan->save()) {
                                                $transaction=new Transaction();
                                                $transaction->user_id = $user->id;
                                                $transaction->status = 'active';
                                                $transaction->transaction_type = 'plan';
                                                $transaction->item_id = $planData->id;
                                                $transaction->txn_id = $txn_id;
                                                $transaction->payment_method = $payment_method;
                                                $transaction->before_wallet_amount = '0.00';
                                                $transaction->after_wallet_amount = '0.00';
                                                $transaction->amount = $planData->amount;
                                                $transaction->title = 'Plan Upgrade';
                                                $transaction->message = 'Plan Upgrade +'.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$planData->amount;
                                                $transaction->save();
                                            }
                                        }
                                    }
                                    $receipt_url = $crr->receipt_url;
                                    //Session::flash('success', 'Payment Successful !');
                                    return view('frontend.subscription.stripe_success',compact('receipt_url'));
                                }else{
                                    return redirect()->route('stripe_failed')->with('success','Subscription is faild.');
                                }
                                //echo json_encode($output); 
                            }else{ 
                                return back()->with('success',$api_error);
                            } 
                        }else{ 
                            return back()->with('success',$api_error);
                        } 
                    }else{ 
                        return back()->with('success',$api_error); 

                    } 
                } 
            }else{
                return back()->with('success','Plan not activate yet.');
            } 
        }else{

            return back()->with('success','Please login first.');
        }
          
          
    }
    public function orderPostOld(Request $request)
    {
         
        try {  
            $user = User::where('id', $request->user_id)->first();
            if ($user) {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $input = $request->all();
                $token =  $request->stripeToken;
                $currency = 'usd';
                $planData = Plan::where('status', 'active')->first();
                if (!empty($planData)) {
                    
                    $booking_number = $this->generateRandomString(6);
                    $planName = $planData->title; 
                    $planPrice = $planData->amount; 
                    $planInterval = 'month';
                    $planPriceCents = round($planPrice*100);
                    // Add customer to stripe
                    if ($user->subscription_id != '') {
                        try { 
                            
                            $customer = \Stripe\Customer::create([ 
                                'name' => ucfirst($user->name),  
                                'id' => ucfirst($user->id)
                            ]);
                        }catch(Exception $e) {   
                            $api_error = $e->getMessage();   
                        }
                    }else{
                        try { 
                            
                            if (empty($customer)) {
                                $customer = \Stripe\Customer::create([ 
                                    'name' => ucfirst($user->name),  
                                    'id' => ucfirst($user->id)
                                ]);
                            }
                        }catch(Exception $e) {   
                            $api_error = $e->getMessage();   
                        }
                    } 
                    if(empty($api_error) && $customer){ 
                        try { 
                            $price = \Stripe\Price::create([ 
                                'unit_amount' => $planPriceCents, 
                                'currency' => 'usd', 
                                'recurring' => ['interval' => $planInterval], 
                                'product_data' => ['name' => $planData->title],
                                
                            ]); 
                        } catch (Exception $e) { 
                            $api_error = $e->getMessage(); 
                        } 
                        if(empty($api_error) && $price){ 
                            // Create a new subscription 
                            try { 
                                if ($user->subscription_id != '') {
                                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                                    $stripe->subscriptions->cancel(
                                      $user->subscription_id,
                                      []
                                    );
                                }
                                $subscription = \Stripe\Subscription::create([ 
                                    'customer' => $customer->id, 
                                    'items' => [[ 
                                        'price' => $price->id, 
                                    ]],

                                      'metadata' => [

                                        'start_date' => date('Y-m-d', strtotime(date('Y-m-d'). ' + 30 days'))

                                      ], 
                                    'payment_behavior' => 'default_incomplete', 
                                    'expand' => ['latest_invoice.payment_intent'], 
                                ]); 
                            }catch(Exception $e) { 
                                $api_error = $e->getMessage(); 
                            } 
                            if(empty($api_error) && $subscription){
                                $user->subscription_id = $subscription->id;
                                $user->save();
                                $output = [ 
                                    'subscriptionId' => $subscription->id, 
                                    'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret, 
                                    'customerId' => $customer->id 
                                ]; 
                                $crr =  Stripe\Charge::create ([
                                        "amount" => $planPriceCents,
                                        "currency" => strtolower($currency),
                                        "source" => $request->stripeToken,
                                        "description" => "Transaction ID : ". $booking_number,
                                ]);
                                if($crr->status=="succeeded"){
                                    $txn_id = $crr->id;
                                    $payment_method = 'stripe';
                                    if ($plan = Plan::where("id", $planData->id)->first()) {
                                        $days = $plan->duration_month;
                                        $user->plan_id = $planData->id;
                                        $user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));  
                                        if ($user->save()) {
                                            $array_old = array('status' => 'old');
                                            UserPlan::where('user_id', $user->id)->update($array_old);
                                            $user_plan = new UserPlan();
                                            $user_plan->user_id = $user->id;
                                            $user_plan->plan_id = $planData->id;
                                            $user_plan->title = $planData->title;
                                            $user_plan->amount = $planData->amount;
                                            $user_plan->duration_text = $planData->duration_text;
                                            $user_plan->duration_month = $planData->duration_month;
                                            $user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));
                                            if ($user_plan->save()) {
                                                $transaction=new Transaction();
                                                $transaction->user_id = $user->id;
                                                $transaction->status = 'active';
                                                $transaction->transaction_type = 'plan';
                                                $transaction->item_id = $planData->id;
                                                $transaction->txn_id = $txn_id;
                                                $transaction->payment_method = $payment_method;
                                                $transaction->before_wallet_amount = '0.00';
                                                $transaction->after_wallet_amount = '0.00';
                                                $transaction->amount = $planData->amount;
                                                $transaction->title = 'Plan Upgrade';
                                                $transaction->message = 'Plan Upgrade +'.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$planData->amount;
                                                $transaction->save();
                                            }
                                        }
                                    }
                                    $receipt_url = $crr->receipt_url;
                                    //Session::flash('success', 'Payment Successful !');
                                    return view('frontend.subscription.stripe_success',compact('receipt_url'));
                                }else{
                                    return redirect()->route('stripe_failed')->with('success','Subscription is faild.');
                                }
                                //echo json_encode($output); 
                            }else{ 
                                return back()->with('success',$api_error);
                            } 
                        }else{ 
                            return back()->with('success',$api_error);
                        } 
                    }else{ 
                        return back()->with('success',$api_error); 

                    } 
                }else{
                    return back()->with('success','Plan not activate yet.');
                }
                 
            }else{

                return back()->with('success','Please login first.');
            }
          
        } catch (Exception $e) {
            return back()->with('success',$e->getMessage());
        }   
    }
    public function stripe_success(Request $request)
    {
        $receipt_url = '';     
        return view('frontend.subscription.stripe_success', compact('receipt_url'));
    } 
    public function stripe_failed(Request $request)
    {     
        return view('frontend.subscription.stripe_failed');
    }
    public function autoDebit(Request $request)
    {   
        //Log::info(['request' => $request]);
        $event = $request->all();
        $eventType = '';
        /*\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $json = @file_get_contents("php://input");
        $file = fopen("app.log", "a");
        fwrite($file, $json);
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent($json, $sig_header, env("WEBHOOK_SECRET_HERE"));
            Log::info(['event' => $event]);
        } catch (\UnexpectedValueException $e) {
            Log::info(['validation' => $e]); 
            
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::info(['signature' => $e]);
            http_response_code(400);
            exit();
        }*/

        if (!empty($event)) {
            //fwrite($file, $event);
            //echo '<pre>'; print_r($event); die;
            if (isset($event['type'])) {
                $eventType = $event['type'];
                $email = $event['data']['object']['billing_details']['email'];
                $paymentIntentId = $event['data']['object']['payment_intent'];
                $amount = $event['data']['object']['amount'];
                $txn_id = $event['data']['object']['balance_transaction'];
                $user_id = $event['data']['object']['customer'];
               // $user_id = 1;
                $stripePaymentStatus = $event['data']['object']['paid'];
            }else{
                //fwrite($file, $event);
                if(isset($event->type)){
                    $eventType = $event->type;
                    $email = $event->data->object->billing_details->email;
                    $paymentIntentId = $event->data->object->payment_intent;
                    $amount = $event->data->object->amount;
                    $txn_id = $event->data->object->balance_transaction;
                    $user_id = $event->data->object->customer;
                   // $user_id = 1;
                    $stripePaymentStatus = $event->data->object->paid;
                }
                
            }
            

            if ($eventType == "charge.payment_failed") {
                $orderStatus = 'Payement Failure';

                $paymentStatus = 'Unpaid';

                $amount = $amount / 100;

                /*$user_id =  $request->user_id; 
                $user = User::where('id', $user_id)->first();
                if ($user) {             
                    $user->plan_id = 0;
                    $user->subscription_id = '';
                    $user->save();
                    
                }*/
            }
            //if ($eventType == "payment_intent.succeeded") {
            if ($eventType == "charge.succeeded") {
                $orderStatus = 'Completed';

                $paymentStatus = 'Paid';

                $amount = $amount / 100;
                $user = User::where('id', $user_id)->first();
                $days = 30;
                if ($user) {
                    if(!Transaction::where("txn_id", $txn_id)->first()){
                        $payment_method = 'stripe';
                        if ($plan = Plan::where("id", 1)->first()) {
                            $days = 30;
                            $user->plan_id = 1;
                            $user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));  
                            if ($user->save()) {
                                $array_old = array('status' => 'old');
                                UserPlan::where('user_id', $user->id)->update($array_old);
                                $user_plan = new UserPlan();
                                $user_plan->user_id = $user->id;
                                $user_plan->plan_id = $plan->id;
                                $user_plan->title = $plan->title;
                                $user_plan->amount = $amount;
                                $user_plan->duration_text = $plan->duration_text;
                                $user_plan->duration_month = $plan->duration_month;
                                $user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));
                                if ($user_plan->save()) {
                                    $transaction=new Transaction();
                                    $transaction->user_id = $user->id;
                                    $transaction->status = 'active';
                                    $transaction->transaction_type = 'plan';
                                    $transaction->item_id = $plan->id;
                                    $transaction->txn_id = $txn_id;
                                    $transaction->payment_method = $payment_method;
                                    $transaction->before_wallet_amount = '0.00';
                                    $transaction->after_wallet_amount = '0.00';
                                    $transaction->amount = $amount;
                                    $transaction->title = 'Recurring Plan Upgrade';
                                    $transaction->message = 'Recurring Plan Upgrade +'.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$amount;
                                    $transaction->save();
                                }
                            }
                        } 
                    }       
                }
                
            }
            http_response_code(200);
        }
    }
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString.strtotime("now");
    }
}
