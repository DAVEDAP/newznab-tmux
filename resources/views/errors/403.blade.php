@extends('errors.layout')

@section('title')
    HTTP/1.1 403 Unathorized
@endsection

@section('content')
    {{ \App\Models\Settings::settingValue('site.main.title') }} <br><br><br>
    <b>You are not logged in!</b> <br>Please <a href='{!! url('/login'); !!}'>login</a>
@endsection
