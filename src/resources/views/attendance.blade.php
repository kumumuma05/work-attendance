@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠登録')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.user_working')

@endsection