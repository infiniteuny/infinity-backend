<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>@yield('title') | {{ config('app.name') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        {{-- CSS --}}
        @yield('css')
        
    </head>
    <body data-spy="scroll" data-target=".site-navbar-target" data-offset="300">
        <!-- Site Wrap  -->
        <div class="site-wrap">
            
            @include('landing.components.navbar')

            @yield('content')

            @include('landing.components.footer')

        </div>
        <!-- .site-wrap -->

        <!-- SCRIPT -->
        @yield('js')
        
    </body>
</html>
