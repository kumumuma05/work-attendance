@extends('layouts.default')

<!-- タイトル -->
@section('title', '修正申請承認')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_correction_approve.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')
    <div class="correction-approve">
        <!-- タイトル -->
        <h1 class="correction-approve__title">
            勤怠詳細
        </h1>

        <!-- 勤務詳細 -->
        <form class="correction-approve__form" action="/stamp_correction_request/approve/{{ $attendanceRequest->id }}" method="post" novalidate>
            @csrf
            <div class="correction-approve__card">
                <!-- 名前 -->
                <div class="correction-approve__row">
                    <dt class="correction-approve__term">名前</dt>
                    <dd class="correction-approve__data">{{ $attendance->user->name }}
                    </dd>
                </div>
                <!-- 日付 -->
                <div class="correction-approve__row">
                    <dt class="correction-approve__term">日付</dt>
                    <dd class="correction-approve__data">
                        <span class="correction-approve__data-year">
                            {{ $attendance->clock_in->isoFormat('Y年') }}
                        </span>
                        <span class="correction-approve__data-monthDay">
                            {{ $attendance->clock_in->isoFormat('M月D日') }}
                        </span>
                    </dd>
                </div>
                <!-- 出勤・退勤 -->
                <div class="correction-approve__row">
                    <dt class="correction-approve__term">出勤・退勤</dt>
                    <dd class="correction-approve__data">
                        <div class="correction-approve__data-row">
                            <span class="correction-approve__time-text">
                                {{ $attendanceRequest->requested_clock_in->format('H:i')}}
                            </span >
                            <span>～</span>
                            <span class="correction-approve__time-text">
                                {{ $attendanceRequest->requested_clock_out->format('H:i') }}
                            </span>
                        </div>
                    </dd>
                </div>
                <!-- 休憩 -->
                @foreach($requestedBreaks as $index => $break)
                    <div class="correction-approve__row">
                        <dt class="correction-approve__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                        <dd class="correction-approve__data">
                            <div class="correction-approve__data-row">
                                <span class="correction-approve__time-text">
                                    {{ optional($break->break_in)->format('H:i') }}
                                </span>
                                @if ($break->break_in || $break->break_out)
                                    <span>～</span>
                                @endif
                                <span class="correction-approve__time-text">
                                    {{ optional($break->break_out)->format('H:i') }}
                                </span>
                            </div>
                        </dd>
                    </div>
                @endforeach
                <!-- 備考 -->
                <div class="correction-approve__row">
                    <dt class="correction-approve__term">備考</dt>
                    <dd class="correction-approve__data">
                        <p class="correction-approve__remark">
                            {{ $attendanceRequest->remarks }}
                        </p>
                    </dd>
                </div>
            </div>
            <!-- 承認ボタン -->
            <div class="correction-approve__button">
                @if (!$isApproved)
                    <button class="correction-approve__form-button" type="submit">承認</button>
                @else
                    <p class="correction-approve__form-message">
                        承認済み
                    </p>
                @endif
            </div>
        </form>
    </div>
@endsection

