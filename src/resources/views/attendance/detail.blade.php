@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠詳細')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.user_working')
    <div class="attendance-detail">
        <!-- タイトル -->
        <h1 class="attendance-detail__title">
            勤怠一覧
        </h1>

        <div class="attendance-detail__row">
            <dt class="attendance-detail__term">名前</dt>
            <dd class="attendance-detail__data">{{ $user->name }}
            </dd>
        </div>

        <div class="attendance-detail__row">
            <dt class="attendance-detail__term">日付</dt>
            <dd class="attendance-detail__data">
                <span class="attendance-detail__data-year">
                    {{ $attendance->date->isoFormat('Y年') }}
                </span>
                <span class="attendance-detail__data-monthday">
                    {{ $attendance->date->isoFormat('M月D日') }}
                </span>
            </dd>
        </div>

        <div class="attendance-detail__row">
            <dt class="attendance-detail__term">出勤・退勤</dt>
            <dd class="attendance-detail__data">
                <input class="attendance-detail__time" type="text" value="{{ $attendance->clock_in->format('H:i') }}">
                <span>～</span>
                <input class="attendance-detail__time" type="text" value="{{ $attendance->clock_out->format('H:i') }}">
            </dd>
        </div>

        <div class="attendance-detail__row">
            @foreach($breaks as $index => $break)
                <dt class="attendance-detail__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                <dd class="attendance-detail__data">
                    <input class="attendance-detail__time" type="text" value="{{ optional($break->break_in)->format('H:i') }}">
                    <span>～</span>
                    <input class="attendance-detail__time" type="text" value="{{ optional($break->break_out)->format('H:i') }}">
                </dd>
            @endforeach
        </div>

        <div class="attendance-detail__row">
            <dt class="attendance-detail__term">備考
            </dt>
            <dd class="attendance-detail__data">
                <input class="attendance-detail__remark" type="text">
            </dd>
        </div>


    </div>
@endsection