@extends('layouts.frontend.app')

@section('content')
  
  
<div class="container">
    <div class="row cms_page">
      <div class="col-md-12">
       <?php /* <h2>{{ ucfirst($cms->name)}}</h2> */?>
        <div class="content">{!! $cms->content !!} </div>
      </div>
    </div>
</div>
 
 @endsection 