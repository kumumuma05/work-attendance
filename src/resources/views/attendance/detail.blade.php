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

        <form class="attendance-detail__form" action="/attendance/detail/test" method="post" novalidate>
            @csrf
            <div class="attendance-detail__row">
                <dt class="attendance-detail__term">名前</dt>
                <dd class="attendance-detail__data">{{ $user->name }}
                </dd>
            </div>

            <div class="attendance-detail__row">
                <dt class="attendance-detail__term">日付</dt>
                <dd class="attendance-detail__data">
                    <span class="attendance-detail__data-year">
                        {{ $attendance->clock_in->isoFormat('Y年') }}
                    </span>
                    <span class="attendance-detail__data-monthday">
                        {{ $attendance->clock_in->isoFormat('M月D日') }}
                    </span>
                </dd>
            </div>

            <div class="attendance-detail__row">
                <dt class="attendance-detail__term">出勤・退勤</dt>
                <dd class="attendance-detail__data">
                    <input class="attendance-detail__time" type="text" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in->format('H:i')) }}" inputmode="numeric">
                    <span>～</span>
                    <input class="attendance-detail__time" type="text" name="requested_clock_out" value="{{ old('requested_clock_out',$attendance->clock_out->format('H:i')) }}" inputmode="numeric">
                    <div class="form__error">
                        @error('requested_clock_in')
                            {{ $message }}<br>
                        @enderror
                        @error('requested_clock_out')
                            {{ $message }}
                        @enderror
                    </div>
                </dd>
            </div>

            <div class="attendance-detail__row">
                @foreach($displayBreaks as $index => $break)
                    <dt class="attendance-detail__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                    <dd class="attendance-detail__data">
                        <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_in]" value="{{ old('requested_breaks.' . $index . '.break_in',optional($break->break_in)->format('H:i')) }}" inputmode="numeric">
                        <span>～</span>
                        <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_out]" value="{{ old('requested_breaks.' . $index . '.break_out', optional($break->break_out)->format('H:i')) }}" inputmode="numeric">
                        <div class="form__error">
                            @error("requested_breaks.$index.break_in")
                                {{ $message }}<br>
                            @enderror
                            @error("requested_breaks.$index.break_out")
                                {{ $message }}
                            @enderror
                        </div>
                    </dd>
                @endforeach
            </div>

            <div class="attendance-detail__row">
                <dt class="attendance-detail__term">備考
                </dt>
                <dd class="attendance-detail__data">
                    <input class="attendance-detail__remark" type="text">
                    <div class="form__error">
                        @error('remarks')
                            {{ $message }}
                        @enderror
                    </div>
                </dd>
            </div>

            <button class="attendance-detail__form-button">修正</button>
        </form>
    </div>
@endsection