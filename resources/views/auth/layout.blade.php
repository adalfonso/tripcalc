@extends('layout')

@section('content')
    <div id="home">
        <div id="splashWrapper">
            <div class="splash4"></div>
            <div class="splash3"></div>
            <div class="splash2"></div>
            <div class="splash1"></div>
        </div>
        <h1 class="wordmark">TripCalc</h1>
        <div id="splash-iconWrapper">
            <img src="img/icon/pinetree-light_blue-256x256.png">
            <img src="img/icon/pisa-light_blue-256x256.png">
            <img src="img/icon/palm_tree-light_blue-256x256.png">
            <img src="img/icon/car-light_blue-256x256.png">
        </div>

        @yield('form')
    </div>
@stop