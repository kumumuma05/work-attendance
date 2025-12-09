@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠一覧(管理者)')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')

    <div class="attendance-list">
        <!-- タイトル -->
        <h1 class="attendance-list__title">
            {{ $currentDay->isoformat('YYYY年M月D日') }}の勤怠
        </h1>

        <!-- 日付の切り替え -->
        <div class="attendance-list__date-nav">
            <a class="attendance-list__date-button" href="{{ url('/admin/attendance/list?date=' . $previousDay) }}">
                <img class="attendance-list__date-arrow" src="{{ asset('images/矢印.png') }}">
                前日
            </a>
            <div class="attendance-list__current-date">
                <img class="attendance-list__calendar-img" src="{{ asset('images/カレンダ.png') }}"alt="">
                <span>{{ $currentDay->format('Y/m/d') }}</span>
            </div>
            <a class="attendance-list__date-button" href="{{ url('/admin/attendance/list?date=' . $nextDay) }}">
                翌日
                <img class="attendance-list__date-arrow" src="{{ asset('images/矢印.png') }}">
            </a>
        </div>

        <!-- テーブル -->
        <div class="attendance-list__table-inner">
            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : ''}}</td>
                            <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : ''}}</td>
                            <td>{{ $attendance->break_duration ? $attendance->break_duration : ''}}</td>
                            <td>{{ $attendance->total_hours }}</td>
                            <td>
                                <a class="attendance-list__detail-link" href="{{ $attendance->id }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection



