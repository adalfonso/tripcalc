<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ Auth::id() }}">

        <title>TripCalc</title>

        <link href="/css/app.css" rel="stylesheet" type="text/css">
        <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,700'
              rel='stylesheet' type='text/css'>
    </head>

    <body id="body">
        <div id="app">
            @if (Session::has('alert'))
                <alert v-if="alert.visible"
                    :message="'{{ Session::get('alert') }}'" @hide="hideAlert">
                </alert>
            @endif

            @if(Auth::check())
                @include('nav.navbar')
                @yield('nav')
            @endif

            <div id="main" class=" clearfix">
                @yield('content')
            </div>
        </div>

        <script src="/js/app.js"></script>

        @yield('js')

        {{-- Default Vue instance. --}}
        @include('defaultVue')

        {{-- Use overwrite directive when declaring new Vue instance. --}}
        @yield('vue')

    </body>
</html>
