@extends('layouts.app')

@section('additionalHead')
    <!-- Scripts -->
    <script type="text/javascript">
        window.serverDate = @json($date);
    </script>
    <script type="text/javascript" src="{{ \App\Utils\Views::mix('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ \App\Utils\Views::mix('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div id="app"></div>
@endsection
