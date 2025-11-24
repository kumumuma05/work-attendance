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
    <div class="attendance">
        <!-- 勤務の状態 -->
        <p class="attendance__state">
            @if($state === 'before_work')
                勤務外
            @elseif($state === 'working')
                勤務中
            @elseif($state === 'on_break')
                休憩中
            @elseif($state === 'after_work')
                退勤済み
            @endif
        </p>
        <!-- 日時表示 -->
        <div class="attendance__date">
            {{ now()->format('Y年n月j日（D）') }}
        </div>
        <div class="attendance__time">
            {{ now()->format('h:m') }}
        </div>
        <!-- 打刻ボタン -->
        <div class="attendance__button">
            <!-- 勤務外 -->
            @if($state === 'before_work')
                <form action="/attendance/clock_in" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--main">出勤</button>
                </form>
            <!-- 勤務中 -->
            @elseif($state === 'working')
                <form action="/attendance/clock_out" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--sub">退勤</button>
                </form>

                <form action="/attendance/break_in" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--main">休憩入</button>
                </form>
            <!-- 休憩中 -->
            @elseif($state === 'on_break')
                <form action="/attendance/break_out" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--main">休憩戻</button>
                </form>
            <!-- 退勤済 -->
            @elseif($state === 'after_work')
                <p class="attendance__done">お疲れ様でした。</p>
            @endif
        </div>
    </div>

@endsection