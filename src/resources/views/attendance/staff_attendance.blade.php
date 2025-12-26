@extends('layouts.default')

<!-- タイトル -->
@section('title', 'スタッフ別勤怠一覧')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff_attendance.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')

    <div class="staff-attendance">
        <!-- タイトル -->
        <h1 class="staff-attendance__title">
            {{ $user->name }}さんの勤怠
        </h1>

        <!-- 月の切り替え -->
        <div class="staff-attendance__date-nav">
            <a class="staff-attendance__date-button" href="{{ url('/admin/attendance/staff/' . $user->id . '?date=' . $previousMonth) }}">
                <img class="staff-attendance__date-arrow" src="{{ asset('images/矢印.png') }}">
                前月
            </a>
            <div class="staff-attendance__current-date">
                <img class="staff-attendance__calendar-img" src="{{ asset('images/カレンダ.png') }}"alt="">
                <span>{{ $currentMonth->format('Y/m') }}</span>
            </div>
            <a class="staff-attendance__date-button" href="{{ url('/admin/attendance/staff/' . $user->id . '?date=' . $nextMonth) }}">
                翌月
                <img class="staff-attendance__date-arrow" src="{{ asset('images/矢印.png') }}">
            </a>
        </div>

        <!-- テーブル -->
        <div class="staff-attendance__table-inner">
            <table class="staff-attendance__table">
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
                            <td>{{ $day['attendance'] ? $day['attendance']->clock_in?->format('H:i') : '' }}</td>
                            <td>{{ $day['attendance'] ? $day['attendance']->clock_out?->format('H:i') : '' }}</td>
                            <td>{{ $day['attendance']->break_duration ?? '' }}</td>
                            <td>{{ $day['attendance']->total_hours ?? '' }}</td>
                            <td>
                                @if ($day['attendance'])
                                    <a  class="staff-attendance__detail-link" href="/admin/attendance/{{$day['attendance']->id }}">詳細</a>
                                @else
                                    <span class="staff-attendance__detail-text">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- CSV出力ボタン -->
        <div class="staff-attendance__button">
            <button class="staff-attendance__button-submit" type="submit">CSV出力</button>
        </div>
    </div>
@endsection



