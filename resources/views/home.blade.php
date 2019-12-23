@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Home</div>

                <div class="card-body">
                    @if (Auth::user()->google2fa_secret)
                    <a href="{{ url('2fa/disable') }}" class="btn btn-warning">Disable 2FA</a>
                    @else
                    <a href="{{ url('2fa/enable') }}" class="btn btn-primary">Enable 2FA</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
