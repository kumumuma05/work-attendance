extends('layouts.default')

<!-- タイトル -->
@section('title', '申請一覧')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.user_working')
    
@endsection