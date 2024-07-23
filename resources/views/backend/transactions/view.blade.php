@extends('layouts.backend.app')
@section('title', 'Transaction - ' . ucfirst($transaction->title))

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Transaction</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('transactions')}}">Transactions</a></li>
                    <li class="breadcrumb-item active">View Transaction</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-lg-4 col-xlg-3 col-md-5">
                <div class="card">
                    <div class="card-body">
                        @include('layouts.backend.message')
                        <center class="m-t-30">
                            <h4 class="card-title m-t-10 m-b-0">
                                @if(isset($transaction->user->id) && !empty($transaction->user->id))
                                  <div class="user_number">
                                    Name :  <a  href="{{ route('viewUsers',$transaction->user->id) }}">
                                    {{ isset($transaction->user->name) && !empty($transaction->user->name) ? ucfirst($transaction->user->name) : '' }}</a> 
                                  </div>
                                  <div class="user_number">
                                    Number : {{ $transaction->user->phone_code.' '.$transaction->user->mobile }}
                                  </div>
                                  @endif
                            </h4>
                        </center>
                    </div>
                    <div>
                        <hr> </div>
                    
                </div>
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">Details</a> </li>

                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile" role="tabpanel">
                            <div class="card-body">
                                <div>
                                    <h6 class="p-t-20">Payment</h6>
                                    <small class="text-primary font-15 db">
                                        <div class="method">
                                            Method - {{ ucfirst($transaction->payment_method) }} 
                                        </div>
                                        <div class="txn_id">
                                            Txn - {{ $transaction->txn_id }}
                                        </div>
                                        <div class="amount">
                                            Amount - ${{ $transaction->amount }}
                                        </div>
                                    </small>
                                    <h6 class="p-t-20">Info</h6>
                                    <small class="text-primary font-15 db">
                                        <div class="plan_title">
                                            Title : {{ ucfirst($transaction->title) }} 
                                        </div>
                                        @php
                                            $item = DB::table('user_plans')->where('plan_id', $transaction->item_id)->first();
                                        @endphp
                                        @if($item)
                                            <div class="user_plan">
                                              Plan : {{ $item->title }}($ {{ $item->amount }})
                                            </div>
                                        @endif
                                    </small>
                                    <h6 class="p-t-20">Message</h6><small class="text-primary font-15 db">{{ ucfirst($transaction->message) }}</small>
                                    <h6 class="p-t-20">Created At</h6><small class="text-primary font-15 db">{{date('Y, M d', strtotime($transaction->created_at))}}</small>
                                    
                                </div>

                                
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')
   
@endpush
