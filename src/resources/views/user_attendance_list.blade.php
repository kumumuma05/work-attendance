@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠一覧')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/user_attendance_list.css') }}">
@endsection

<!-- ヘッダー -->
@section('header')
    @include('header.user_working')
@endsection

<!-- 本体 -->
@section('content')
    <div class="attendance-list">
        <!-- タイトル -->
        <h1 class="attendance-list__title">
            勤怠一覧
        </h1>

        <!-- 月の切り替え -->
        <div class="attendance-list__date-nav">
            <a class="attendance-list__date-button" href="{{ url('/attendance/list?date=' . $previousMonth) }}">
                <img class="attendance-list__date-arrow" src="{{ asset('images/矢印.png') }}">
                前月
            </a>
            <div class="attendance-list__current-date">
                <img class="attendance-list__calendar-img" src="{{ asset('images/カレンダ.png') }}"alt="カレンダー">
                <span>{{ $currentMonth->format('Y/m') }}</span>
            </div>
            <a class="attendance-list__date-button" href="{{ url('/attendance/list?date=' . $nextMonth) }}">
                翌月
                <img class="attendance-list__date-arrow" src="{{ asset('images/矢印.png') }}">
            </a>
        </div>

        <!-- テーブル -->
        <div class="attendance-list__table-inner">
            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($calendar as $day)
                        <tr>
                            <td>{{ $day['date']->isoFormat('MM/DD(ddd)') }}</td>
                            <td>{{ $day['attendance'] ? $day['attendance']->clock_in?->format('H:i') : ''}}</td>
                            <td>{{ $day['attendance'] ? $day['attendance']->clock_out?->format('H:i') : ''}}</td>
                            <td>{{ $day['attendance']->break_duration ?? ''}}</td>
                            <td>{{ $day['attendance']->total_hours ?? '' }}</td>
                            <td>
                                @if ($day['attendance'])
                                    <a class="attendance-list__detail-link" href="/attendance/detail/{{ $day['attendance']->id }}">詳細</a>
                                @else
                                    <span class="attendance-list__detail-text">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection



