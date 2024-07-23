@extends('layouts.backend.app')
@section('title', 'Dashboard')

@section('content')
	<div class="content-wrapper">
        <div class="row">
            
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class=" fa fa-user text-danger icon-lg"></i>
                            </div>
                            <div class="float-right">
                                <p class="mb-0 text-right">Total Users</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{$total_user}}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-alert-octagon mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-rocket text-warning icon-lg"></i>
                            </div>
                            <div class="float-right">
                                <p class="mb-0 text-right">Total Plan</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">1</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-list-alt text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Total Location</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{ $total_Dispensary }}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-list-alt text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Cat/Type/Strain</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{ $total_Category }}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-product-hunt text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Total Product</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{ $total_Product }}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-grav text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Total CMS</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">3</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-support text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Support Request</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{ $total_Support }}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="fa fa-comments-o text-warning icon-lg"></i>
                            </div>
                            <div class="float-right" style="width:70%">
                                <p class="mb-0 text-right">Feedback Request</p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0">{{ $total_Feedback }}</h3>
                                </div>
                            </div>
                        </div>
                        {{-- <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-bookmark-outline mr-1" aria-hidden="true"></i> 65% lower growth
                        </p> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6 mt-sm-0 mt-6">
              <div class="card overflow-hidden" style="height:330px;">
                <div class="card-header p-3 pb-0">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold">Income </p>
                  <h5 class="font-weight-bolder mb-0">
                        ${{$total_amount}}
                    <span class="text-success text-sm font-weight-bolder">+100%</span>
                    
                  </h5>
                  <!--<p class="mb-0">chart showing current year record</p>-->
                </div>
                <div class="card-body p-0">
                  <div class="chart chart-area">
                    <!-- <canvas id="chart-line-2" class="chart-canvas" height="100"></canvas> -->
                    <canvas id="myAreaChart" class="chart-canvas" height="200"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-sm-6 mt-sm-0 mt-6">
              <div class="card overflow-hidden" style="height:330px;">
                  <div class="card-header p-3 pb-0">
                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Users</p>
                    <h5 class="font-weight-bolder mb-0">
                      {{ $total_user }}
                      <span class="text-success text-sm font-weight-bolder">+100%</span>
                    </h5>
                    <!--<p class="mb-0">chart showing last 7 days record</p>-->
                  </div>
                  <div class="card-body p-0">
                    <div class="chart">
                      <!-- <canvas id="chart-line-1" class="chart-canvas" height="100"></canvas> -->
                      <div id="pie_chart" height="100" class="chart-canvas"></div>
                    </div>
                  </div>
              </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
      <!-- Page level plugins -->
<script src="{{asset('js/off-canvas.js')}}"></script>
<script src="{{asset('plugins/Chart.js/Chart.bundle.min.js')}}"></script> 
<!-- <script src="{{asset('plugins/Chart.js/Chart.min.js')}}"></script> -->
<script src="{{asset('plugins/Chart.js/axios.min.js')}}"></script>
<script type="text/javascript" src="{{asset('plugins/Chart.js/loader.js')}}"></script>
<!-- <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
{{-- pie chart --}}
<script type="text/javascript">
  var analytics = <?php echo $users; ?>

  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart()
  {
      var data = google.visualization.arrayToDataTable(analytics);
      var options = {
          title : 'Last 30 Days registered user'
      };
      var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
      chart.draw(data, options);
  }
</script>
  {{-- line chart --}}
  <script type="text/javascript">
    const url = "{{ route('Income') }}";
    Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    function number_format(number, decimals, dec_point, thousands_sep) {
      number = (number + '').replace(',', '').replace(' ', '');
      var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
          var k = Math.pow(10, prec);
          return '' + Math.round(n * k) / k;
        };
      s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
      if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
      }
      if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    }

      // Area Chart Example
      var ctx = document.getElementById("myAreaChart");

        axios.get(url)
              .then(function (response) {
                const data_keys = Object.keys(response.data);
                const data_values = Object.values(response.data);
                var myLineChart = new Chart(ctx, {
                  type: 'line',
                  data: {
                    labels: data_keys, // ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                      label: "Earnings",
                      lineTension: 0.3,
                      backgroundColor: "rgba(78, 115, 223, 0.05)",
                      borderColor: "rgba(78, 115, 223, 1)",
                      pointRadius: 3,
                      pointBackgroundColor: "rgba(78, 115, 223, 1)",
                      pointBorderColor: "rgba(78, 115, 223, 1)",
                      pointHoverRadius: 3,
                      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                      pointHitRadius: 10,
                      pointBorderWidth: 2,
                      data:data_values,// [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                    }],
                  },
                  options: {
                    maintainAspectRatio: false,
                    layout: {
                      padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                      }
                    },
                    scales: {
                      xAxes: [{
                        time: {
                          unit: 'date'
                        },
                        gridLines: {
                          display: false,
                          drawBorder: false
                        },
                        ticks: {
                          maxTicksLimit: 7
                        }
                      }],
                      yAxes: [{
                        ticks: {
                          maxTicksLimit: 5,
                          padding: 10,
                          // Include a dollar sign in the ticks
                          callback: function(value, index, values) {
                            return '$' + number_format(value);
                          }
                        },
                        gridLines: {
                          color: "rgb(234, 236, 244)",
                          zeroLineColor: "rgb(234, 236, 244)",
                          drawBorder: false,
                          borderDash: [2],
                          zeroLineBorderDash: [2]
                        }
                      }],
                    },
                    legend: {
                      display: false
                    },
                    tooltips: {
                      backgroundColor: "rgb(255,255,255)",
                      bodyFontColor: "#858796",
                      titleMarginBottom: 10,
                      titleFontColor: '#6e707e',
                      titleFontSize: 14,
                      borderColor: '#dddfeb',
                      borderWidth: 1,
                      xPadding: 15,
                      yPadding: 15,
                      displayColors: false,
                      intersect: false,
                      mode: 'index',
                      caretPadding: 10,
                      callbacks: {
                        label: function(tooltipItem, chart) {
                          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                          return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
                        }
                      }
                    }
                  }
                });
              })
              .catch(function (error) {
              //   vm.answer = 'Error! Could not reach the API. ' + error
              console.log(error)
              });

  </script>
@endpush