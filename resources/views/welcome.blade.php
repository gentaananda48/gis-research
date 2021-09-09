@extends('layouts.admin')

@section('content')
<style>

</style>

        <div class="page-title">
          <div>
            <h1><i class="fa fa-dashboard"></i> Home</h1>
            <p>this is example of home 
        <?php print bcrypt("admin"); ?></p>
          </div>
          <div>
            <ul class="breadcrumb">
              <li><i class="fa fa-home fa-lg"></i></li>
              <li><a href="#">Home</a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3">
            <div class="widget-small label-warning"><i class="icon fa fa-send fa-3x"></i>
              <div class="info">
                <h4>Sent</h4>
                <p><b>5</b></p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="widget-small label-info"><i class="icon fa fa-check fa-3x"></i>
              <div class="info">
                <h4>Approved</h4>
                <p><b>25</b></p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="widget-small label-success"><i class="icon fa fa-dollar fa-3x"></i>
              <div class="info">
                <h4>Reimbursed</h4>
                <p><b>10</b></p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="widget-small label-danger"><i class="icon fa fa-close fa-3x"></i>
              <div class="info">
                <h4>Rejected</h4>
                <p><b>500</b></p>
              </div>
            </div>
          </div>
        </div>
@endsection