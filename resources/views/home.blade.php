@extends('base_theme')
 
@section('style')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.41.0/apexcharts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tilt.js/1.2.1/tilt.jquery.min.js"></script>
<style>
 /* Define the color palette */
    :root {
        --primary-color: #00a65a;  /* green */
        --secondary-color: #acc6aa; /* s */
        --third-color: #ffbd67; /* sc */
        --background-color: #f2f2f2;
        --text-color: #333333;
        --semi-color: #118a7e;
    }
 
    body {
        background-color: var(--background-color);
        color: var(--text-color);
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
 
    /* Override Bootstrap card styles */
    .card {
        margin-bottom: 20px;
        margin-top: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
        position: relative;
        background-color: #fdfff0;
        border-radius: 10px;
        padding: 15px;
        height: 410px; /* Increase the height for better visualization */
    }

    .card:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
 
    /* Typography */
    h5.card-title {
        position: relative;
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
        color: var(--semi-color);
        text-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    }
 
    .card-text {
        font-size: 36px;
        font-weight: bold;
        text-align: center;
        color: var(--primary-color);
    }
 
    /* Charts */
    .chart-container {
        width: 100%;
        height: 380px; /* Increase the height for better visualization */
        margin-left: 0;
        margin-right: auto;
    }
 
    /* Rotating Circle */
    .rotating-circle {
        position: absolute;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 10px solid var(--secondary-color);
        animation: rotate 5s linear infinite;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
 
    .rotating-circle span {
        display: block;
        font-size: 48px;
        font-weight: bold;
        color: var(--secondary-color);
        animation: number-increase 5s linear infinite;
    }

        /* Add styling for the tilt effect */
    .tilt {
        transition: transform 0.3s;
    }

    .tilt:hover {
        transform: scale(1.05);
    }

    /* Style the section titles */
    .section-title {
        position: relative;
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        color: var(--semi-color);
        text-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    }

    .card-vat {
        margin-bottom: 10px;
        margin-top: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
        position: relative;
        background-color: #fdfff0;
        border-radius: 18px;
        padding: 15px;
    }
 
</style>
@endsection
 
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-4 text-center">
            <div class="card-vat">
                <h2 class="section-title">Dashboard VAT {{$formattedYesterday}}</h2>
            </div>
        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL APLIKASI</h5>
                    <div class="chart-container">
                        <div id="charts"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Card 3 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL UNIT AKTIF</h5>
                    <div class="chart-container">
                        <div id="chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-md-6">
            <!-- Card 5 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL APLIKASI PER-SHIFT</h5>
                    <div class="chart-container">
                        <div id="chart5"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Card 3 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">JENIS APLIKASI</h5>
                    <div class="chart-container">
                        <div id="chart3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-md-12">
            <!-- Card 2 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL LOKASI PER-UNIT DATA</h5>
                    <div class="chart-container">
                        <div id="chart2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 
<div>
  @section('field')
 
  @endsection
</div>
@endsection
@php
    // Convert the JSON-encoded data back to a JavaScript object
    $chartData = json_decode($chartDataJSON);
@endphp
 
 
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.41.0/apexcharts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tilt.js/1.2.1/tilt.jquery.min.js"></script>
    <!-- ApexCharts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        // var dummyChartData = {
        //         series: [
        //             {
        //                 name: 'Series 1',
        //                 data: [40, 60, 35, 80, 50, 70], // Replace this array with your desired data
        //             },
        //         ],
        //         categories: ['Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5', 'Category 6'], // Replace this array with your desired categories
        //     };
 
        // chart to show data from card number 1
        var options2 = {
          series: [<?php echo $result2[0]->Lokasi_Count; ?>],
          chart: {
          height: 350,
          type: 'radialBar',
          offsetY: -10
        },
        plotOptions: {
          radialBar: {
            startAngle: -135,
            endAngle: 135,
            dataLabels: {
              name: {
                fontSize: '16px',
                color: 'var(--semi-color)', 
                offsetY: 120
              },
              value: {
                offsetY: 76,
                fontSize: '22px',
                color: undefined,
                formatter: function (val) {
                  return val;
                }
              }
            }
          }
        },
        fill: {
          type: 'gradient',
          gradient: {
              shade: 'dark',
              shadeIntensity: 0.15,
              inverseColors: false,
              opacityFrom: 1,
              opacityTo: 1,
              stops: [0, 50, 65, 91]
          },
        },
        stroke: {
          dashArray: 4
        },
        labels: ['Total Application'],
        };
 
        var chart = new ApexCharts(document.querySelector("#charts"), options2);
        chart.render();
 
        // chart to show data from card number 2
        var options = {
          series: [<?php echo $result1[0]->Unit_Aktif; ?>],
          chart: {
          height: 350,
          type: 'radialBar',
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          radialBar: {
            startAngle: -135,
            endAngle: 225,
             hollow: {
              margin: 0,
              size: '70%',
              background: '#fff',
              image: undefined,
              imageOffsetX: 0,
              imageOffsetY: 0,
              position: 'front',
              dropShadow: {
                enabled: true,
                top: 3,
                left: 0,
                blur: 4,
                opacity: 0.24
              }
            },
            track: {
              background: '#fff',
              strokeWidth: '67%',
              margin: 0, // margin is in pixels
              dropShadow: {
                enabled: true,
                top: -3,
                left: 0,
                blur: 4,
                opacity: 0.35
              }
            },
 
            dataLabels: {
              show: true,
              name: {
                offsetY: -10,
                show: true,
                color: '#888',
                fontSize: '17px'
              },
              value: {
                formatter: function(val) {
                  return parseInt(val);
                },
                color: '#111',
                fontSize: '36px',
                show: true,
              }
            }
          }
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: ['#ABE5A1'],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
          }
        },
        stroke: {
          lineCap: 'round'
        },
        labels: ['Unit Aktif'],
        };
 
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
 
            // Enable Tilt.js for cards number 1 and 4
            $('.card-vat').tilt({
                scale: 1.05,
                perspective: 1000,
                easing: 'cubic-bezier(.03,.98,.52,.99)',
            });
 
            // Query 2: Total Location per Unit Data
            // var dummyData2 = [50, 30, 40, 70, 20, 50]; 
            // var dumyCategories2 = ['Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5', 'Category 6'];

            var chart2Options = {
                chart: {
                    type: 'line',
                    height: 300,
                    width: '100%',
                    fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 1500,
                        animateGradually: {
                            enabled: true,
                            delay: 200,
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350,
                        },
                    },
                    toolbar: {
                        show: false,
                    },                
                },
                series: [
                    {
                        name: 'Unit Aktif',
                        data: @json($data),
                    },
                ],
                xaxis: {
                    categories: @json($labels),
                    labels: {
                        style: {
                            colors: 'var(--primary-color)',
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: 'var(--primary-color)',
                            fontSize: '12px',
                        },
                    },
                },
                stroke: {
                colors: 'var(--primary-color)',
                width: 2,
            },
                markers: {
                    size: 4, 
                    colors: 'var(--primary-color)', 
                    strokeWidth: 0, 
                    strokeColors: 'transparent', 
                    hover: {
                        size: 6,
                    },
                },
            };
            var chart2 = new ApexCharts(document.querySelector("#chart2"), chart2Options);
            chart2.render();
 
            var chartData = @json($chartData);
 
            // Query 3: Jenis Aplikasi in One Day
            var chart3Options = {
                chart: {
                    type: 'bar',
                    height: 300,
                    width: '100%',
                    fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 1500,
                        animateGradually: {
                            enabled: true,
                            delay: 200,
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350,
                        },
                    },
                },
                series: chartData.series,
                    chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                            show: false,
                        },
                    },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        colors: {
                            ranges: [
                                {
                                    from: 0,
                                    to: Infinity,
                                    color: 'var(--primary-color)', 
                                },
                            ],
                        },
                    },
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: chartData.categories,
                    labels: {
                        formatter: function (val) {
                        return val.toFixed(2);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                        return val.toFixed(2);
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    show: false 
                },
            };
 
            var chart3 = new ApexCharts(document.querySelector("#chart3"), chart3Options);
            chart3.render();
 
            // var dummyData = [50, 30, 40, 70, 20, 50]; // Replace this array with your desired data
            // var dumyCategories = ['Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5', 'Category 6'];
 
            // Query 5: Total Aplikasi per Shift
            var chart5Options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    width: '100%',
                    fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 1500,
                        animateGradually: {
                            enabled: true,
                            delay: 200,
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350,
                        },
                    },
                    toolbar: {
                        show: false, 
                    },
                },
                series: [
                    {
                        name: 'Total shift',
                        data: @json($data2),
                    },
                ],
                xaxis: {
                    categories: @json($labels2),
                    labels: {
                        style: {
                            colors: 'var(--primary-color)',
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: 'var(--primary-color)',
                            fontSize: '12px',
                        },
                    },
                },
                plotOptions: {
                    bar: {
                        colors: {
                            ranges: [
                                {
                                    from: 0,
                                    to: Infinity,
                                    color: 'var(--primary-color)',
                                },
                            ],
                        },
                    },
                },
                legend: {
                    show: false 
                },
            };
            var chart5 = new ApexCharts(document.querySelector("#chart5"), chart5Options);
            chart5.render();
 
            // animated javascript for increasing data
                function animateCount(element, targetValue) {
                gsap.to(element, {
                    duration: 1,
                    innerHTML: targetValue,
                    roundProps: 'innerHTML',
                    ease: 'power1.out',
                });
            }
 
            function updateCount(element, targetValue) {
                var count = 0;
                function update() {
                    if (count < targetValue) {
                        count += 1;
                        animateCount(element, count);
                        requestAnimationFrame(update);
                    }
                }
                requestAnimationFrame(update);
            }
 
            // Start the animation for Card 1
            var query1Count = document.getElementById('query1Count');
            var targetValue1 = parseInt(query1Count.innerHTML);
            updateCount(query1Count, targetValue1);
 
            // Start the animation for Card 4
            var query4Count = document.getElementById('query4Count');
            var targetValue4 = parseInt(query4Count.innerHTML);
            updateCount(query4Count, targetValue4);
 
        });
    </script>
@endsection