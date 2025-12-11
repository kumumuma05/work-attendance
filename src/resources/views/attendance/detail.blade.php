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
            勤怠詳細
        </h1>

        <!-- 勤務詳細 -->
        <form class="attendance-detail__form" action="/attendance/detail/test" method="post" novalidate>
            @csrf
            <div class="attendance-detail__card">
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
                                <input class="attendance-detail__time" type="text" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in->format('H:i')) }}" inputmode="numeric">
                                <span>～</span>
                                <input class="attendance-detail__time" type="text" name="requested_clock_out" value="{{ old('requested_clock_out',$attendance->clock_out->format('H:i')) }}" inputmode="numeric">
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
                                    <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_in]" value="{{ old('requested_breaks.' . $index . '.break_in',optional($break->break_in)->format('H:i')) }}" inputmode="numeric">
                                    <span>～</span>
                                    <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_out]" value="{{ old('requested_breaks.' . $index . '.break_out', optional($break->break_out)->format('H:i')) }}" inputmode="numeric">
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
                            <textarea class="attendance-detail__remark" name="remarks">
                                {{ old('remarks', $attendance->remarks ?? '') }}
                            </textarea>
                            @error('remarks')
                                <div class="form__error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </dd>
                </div>
            </div>

            <div class="attendance-detail__button">
                <button class="attendance-detail__form-button">修正</button>
            </div>
        </form>
    </div>
@endsection