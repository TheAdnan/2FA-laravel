<?php
if(!isset($enter_token))
    $enter_token = false;
?>
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="alert alert-warning">
                    <?= $error ?>
                </div>
            </div>
        </div>
        @if($enter_token)
            <div class="row panel-body">
                <form method="POST" action="{{ route('validateToken') }}">
                    {{ csrf_field() }}
                    <div class="col-md-6">
                        <input id="sms_token" class="form-control" name="sms_token" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            Enter SMS token
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
