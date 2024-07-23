@extends('layouts.backend.app')
@section('title', 'Edit Subscription - '.$plan->name)

@section('content')
    <div class="content-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{ucfirst($type)}} Subscription</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('plan')}}">Subscription</a></li>
                    <li class="breadcrumb-item active">Edit Subscription</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @include('layouts.backend.message')
                    <div class="card-body">

                        <h4>Edit Subscription</h4>
                        <hr>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="form-material m-t-50 row form-valide" method="post" action="{{route('updatePlan',['id'=>$plan->id])}}" enctype="multipart/form-data">

                            {{csrf_field()}}
                            <div class="form-group col-md-12 m-t-20">
                                <label>Title</label><sup class="text-reddit"> *</sup>
                                <input type="text" class="form-control form-control-line" name="title" value="{{old($plan->title, $plan->title)}}">
                            </div>
                            <div class="form-group col-md-12 m-t-20">
                                <label>Price($)</label><sup class="text-reddit"> *</sup>
                                <input type="number" class="form-control form-control-line numberInput" min="1" name="amount" placeholder="Enter price" value="{{ $plan->amount }}">
                            </div>
                            <div class="form-group col-md-12 m-t-20" style="display: none;">
                                <label>Duration</label><sup class="text-reddit"> *</sup>
                                <select name="duration_month" class="form-control form-control-line">
                                    <option value="1" @if($plan->duration_month == '1') selected @endif>Daily</option>
                                    <option value="30" @if($plan->duration_month == '30') selected @endif>Monthly</option>
                                    <option value="90" @if($plan->duration_month == '90') selected @endif>Quarterly</option>
                                    <option value="180" @if($plan->duration_month == '180') selected @endif>Semi Annually</option>
                                    <option value="365" @if($plan->duration_month == '365') selected @endif>Annual</option>
                                </select>
                            </div>
                            <div class="col-12 m-t-20">
                                <button type="submit" class="btn btn-success submitBtn m-r-10">Save</button>
                                <a href="{{route('plan')}}" class="btn btn-inverse waves-effect waves-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(function(){
            $(document).on('keyup',".decimalInput, .numberInput",function(e){

                if($(this).val().indexOf('-') >=0){
                    $(this).val($(this).val().replace(/\-/g,''));
                }
            })

            $(document).find(".numberInput").maskAsNumber({receivedMinus:false});
            $(document).find(".decimalInput").maskAsNumber({receivedMinus:false,decimals:6});
            

            
        });
</script>
@endpush