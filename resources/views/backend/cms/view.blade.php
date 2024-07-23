@extends('layouts.backend.app')
@section('title', 'CMS - ' . $cms->name)

@section('content')
    <div class="content-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-primary">{{$cms->name}}</h3></div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('cms')}}">CMS</a></li>
                    <li class="breadcrumb-item active">{{$cms->name}}</li>
                </ol>
            </div>
        </div>
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">

                @if($cms->slug == 'faq' || $cms->slug == 'delivery-faq')
                    <div class="card p-10">
                        <div class="dt-buttons float-right">
                            {{-- <a href="javascript:void(0)" class="btn dt-button toolTip m-l-20 float-right addFaq" data-toggle="tooltip" data-placement="bottom" title="Add FAQ">Add FAQ</a> --}}
                        </div>
                        <div class="card-body">
                            @foreach($faqs as $faq)
                                <div class="faqQnA" id="faqQnA_{{$faq->id}}">
                                    {{-- <a href="javascript:void(0)" class="toolTip m-l-20 float-right deleteQnA" data-toggle="tooltip" data-placement="bottom" id="deleteqna_{{$faq->id}}" title="Delete">Delete</a> --}}
                                    <div contenteditable="false" placeholder="Edit Question" class="w-100 questions m-t-10 m-b-20" id="question_{{$faq->id}}" style="font-weight: bold">
                                        {!! $faq->question !!}
                                    </div>
                                    <div class="answers m-b-40" id="answer_{{$faq->id}}">
                                        {!! $faq->answer !!}
                                    </div>
                                    <hr>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="card">
                        @include('layouts.backend.message')
                        <div class="card-body">
                            <div class="click2edit m-b-40">
                                {!! $cms->content !!}
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        </div>
        <!-- End PAge Content -->
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">

    </script>
@endpush
