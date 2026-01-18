@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠詳細')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/user_attendance_detail.css') }}">
@endsection

<!-- ヘッダー -->
@section('header')
    @include('header.user_working')
@endsection

<!-- 本体 -->
@section('content')
    <div class="attendance-detail">
        <!-- タイトル -->
        <h1 class="attendance-detail__title">
            勤怠詳細
        </h1>

        <!-- 勤務詳細 -->
        <form class="attendance-detail__form" action="/attendance/detail/{{ $attendance->id }}" method="post" novalidate>
            @csrf
            <dl class="attendance-detail__card">
                <!-- 名前 -->
                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">名前</dt>
                    <dd class="attendance-detail__data">{{ $user->name }}
                    </dd>
                </div>
                <!-- 日付 -->
                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">日付</dt>
                    <dd class="attendance-detail__data">
                        <span class="attendance-detail__data-year">
                            {{ $attendance->clock_in->isoFormat('Y年') }}
                        </span>
                        <span class="attendance-detail__data-monthDay">
                            {{ $attendance->clock_in->isoFormat('M月D日') }}
                        </span>
                    </dd>
                </div>
                <!-- 出勤・退勤 -->
                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">出勤・退勤</dt>
                    <dd class="attendance-detail__data">
                        <div class="attendance-detail__data-wrap">
                            <div class="attendance-detail__data-row">
                                @if (!$hasPendingRequest)
                                    <input class="attendance-detail__time-input" type="text" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in->format('H:i')) }}" inputmode="numeric">
                                @else
                                    <span class="attendance-detail__time-text">
                                        {{ $displayClockIn }}
                                    </span>
                                @endif
                                <span>～</span>
                                @if (!$hasPendingRequest)
                                    <input class="attendance-detail__time-input" type="text" name="requested_clock_out" value="{{ old('requested_clock_out',optional($attendance->clock_out)->format('H:i')) }}" inputmode="numeric">
                                @else
                                    <span class="attendance-detail__time-text">
                                    {{ $displayClockOut }}
                                    </span>
                                @endif
                            </div>
                            @error('requested_clock_in')
                                <div class="form__error">
                                    {{ $message }}
                                </div>
                            @enderror
                            @error('requested_clock_out')
                                <div class="form__error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </dd>
                </div>
                <!-- 休憩 -->
                @foreach($displayBreaks as $index => $break)
                    <div class="attendance-detail__row">
                        <dt class="attendance-detail__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                        <dd class="attendance-detail__data">
                            <div class="attendance-detail__data-wrap">
                                <div class="attendance-detail__data-row">
                                    @if (!$hasPendingRequest)
                                        @if (!empty($break->id))
                                            <input type="hidden" name="requested_breaks[{{ $index }}][break_id]" value="{{ $break->id ?? '' }}">
                                        @endif
                                        <input class="attendance-detail__time-input" type="text" name="requested_breaks[{{ $index }}][break_in]" value="{{ old('requested_breaks.' . $index . '.break_in',$break->break_in) }}" inputmode="numeric">
                                    @else
                                        <span class="attendance-detail__time-text">
                                            {{ $break->break_in }}
                                        </span>
                                    @endif
                                    <span>～</span>
                                    @if (!$hasPendingRequest)
                                        <input class="attendance-detail__time-input" type="text" name="requested_breaks[{{ $index }}][break_out]" value="{{ old('requested_breaks.' . $index . '.break_out', $break->break_out) }}" inputmode="numeric">
                                    @else
                                        <span class="attendance-detail__time-text">
                                            {{ $break->break_out }}
                                        </span>
                                    @endif
                                </div>
                                @error("requested_breaks.$index.break_in")
                                    <div class="form__error">
                                        {{ $message }}
                                    </div>
                                @enderror
                                @error("requested_breaks.$index.break_out")
                                    <div class="form__error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </dd>
                    </div>
                @endforeach
                <!-- 備考 -->
                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">備考</dt>
                    <dd class="attendance-detail__data">
                        <div class="attendance-detail__data-wrap">
                            @if (!$hasPendingRequest)
                                <textarea class="attendance-detail__remark-text" name="remarks">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
                            @else
                                <div class="attendance-detail__remark">
                                    {{ $pendingRequest->remarks }}
                                </div>
                            @endif
                            @error('remarks')
                                <div class="form__error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </dd>
                </div>
            </dl>

            <div class="attendance-detail__button">
                @if (!$hasPendingRequest)
                    <button class="attendance-detail__form-button">修正</button>
                @else
                    <p class="attendance-detail__form-message">
                        *承認待ちのため修正はできません。
                    </p>
                @endif
            </div>
        </form>
    </div>
@endsection