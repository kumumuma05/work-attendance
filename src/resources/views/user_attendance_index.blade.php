@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠登録')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/user_attendance_index.css') }}">
@endsection

<!-- 本体 -->
@section('content')

    <!-- ヘッダー -->
    @if($status === 'after_work')
        @include('header.user_off')
    @else
        @include('header.user_working')
    @endif

    <div class="attendance">
        <!-- 勤務の状態 -->
        <p class="attendance__status">
            @if($status === 'before_work')
                勤務外
            @elseif($status === 'working')
                出勤中
            @elseif($status === 'on_break')
                休憩中
            @elseif($status === 'after_work')
                退勤済
            @endif
        </p>
        <!-- 日時表示 -->
        <div class="attendance__date">
            {{ now()->isoFormat('Y年M月D日(ddd)') }}
        </div>
        <div class="attendance__time">
            {{ now()->format('H:i') }}
        </div>
        <!-- 打刻ボタン -->
        <div class="attendance__action">
            <!-- 勤務外 -->
            @if($status === 'before_work')
                <form action="/attendance/clock_in" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--main">出勤</button>
                </form>
            <!-- 勤務中 -->
            @elseif($status === 'working')
                <form action="/attendance/clock_out" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--main">退勤</button>
                </form>

                <form action="/attendance/break_in" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--sub">休憩入</button>
                </form>
            <!-- 休憩中 -->
            @elseif($status === 'on_break')
                <form action="/attendance/break_out" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--sub">休憩戻</button>
                </form>
            <!-- 退勤済 -->
            @elseif($status === 'after_work')
                <p class="attendance__done">お疲れ様でした。</p>
            @endif
        </div>
    </div>

@endsection