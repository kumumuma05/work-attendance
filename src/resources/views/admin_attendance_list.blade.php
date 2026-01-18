@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠一覧(管理者)')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

<!-- ヘッダー -->
@section('header')
    @include('header.admin')
@endsection

<!-- 本体 -->
@section('content')
    <div class="admin-list">
        <!-- タイトル -->
        <h1 class="admin-list__title">
            {{ $currentDay->isoFormat('YYYY年M月D日') }}の勤怠
        </h1>

        <!-- 日付の切り替え -->
        <div class="admin-list__date-nav">
            <a class="admin-list__date-button" href="{{ url('/admin/attendance/list?date=' . $previousDay) }}">
                <img class="admin-list__date-arrow" src="{{ asset('images/矢印.png') }}">
                前日
            </a>
            <div class="admin-list__current-date">
                <img class="admin-list__calendar-img" src="{{ asset('images/カレンダ.png') }}"alt="カレンダー">
                <span>{{ $currentDay->format('Y/m/d') }}</span>
            </div>
            <a class="admin-list__date-button" href="{{ url('/admin/attendance/list?date=' . $nextDay) }}">
                翌日
                <img class="admin-list__date-arrow" src="{{ asset('images/矢印.png') }}">
            </a>
        </div>

        <!-- テーブル -->
        <div class="admin-list__table-inner">
            <table class="admin-list__table">
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
                                <a class="admin-list__detail-link" href="/admin/attendance/{{ $attendance->id }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection



