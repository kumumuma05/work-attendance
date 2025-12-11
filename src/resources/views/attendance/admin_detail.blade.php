@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠一覧(管理者)')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')
    <div class="attendance-detail">
        <!-- タイトル -->
        <h1 class="attendance-detail__title">
            勤怠詳細
        </h1>

        <!-- 勤務詳細 -->
        <form class="attendance-detail__form" action="/attendance/detail/test" method="post" novalidate>
            @csrf
            <div class="attendance-detail__card">
                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">名前</dt>
                    <dd class="attendance-detail__data">{{ $attendance->user->name }}
                    </dd>
                </div>

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

                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">出勤・退勤</dt>
                    <dd class="attendance-detail__data attendance-detail__dat--column">
                        <div class="attendance-detail__date-row">
                            <input class="attendance-detail__time" type="text" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in->format('H:i')) }}" inputmode="numeric">
                            <span>～</span>
                            <input class="attendance-detail__time" type="text" name="requested_clock_out" value="{{ old('requested_clock_out',$attendance->clock_out->format('H:i')) }}" inputmode="numeric">
                        </div>
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

                @foreach($displayBreaks as $index => $break)
                    <div class="attendance-detail__row">
                        <dt class="attendance-detail__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                        <dd class="attendance-detail__date attendance-detail__date--column">
                            <div class="attendance-detail__date-row">
                                <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_in]" value="{{ old('requested_breaks.' . $index . '.break_in',optional($break->break_in)->format('H:i')) }}" inputmode="numeric">
                                <span>～</span>
                                <input class="attendance-detail__time" type="text" name="requested_breaks[{{ $index }}][break_out]" value="{{ old('requested_breaks.' . $index . '.break_out', optional($break->break_out)->format('H:i')) }}" inputmode="numeric">
                            </div>
                            <div class="form__error">
                                @error("requested_breaks.$index.break_in")
                                    {{ $message }}<br>
                                @enderror
                                @error("requested_breaks.$index.break_out")
                                    {{ $message }}
                                @enderror
                            </div>
                        </dd>
                    </div>
                @endforeach

                <div class="attendance-detail__row">
                    <dt class="attendance-detail__term">備考
                    </dt>
                    <dd class="attendance-detail__date attendance-detail__date--column">
                        <textarea class="attendance-detail__remark" name="remarks">
                            {{ old('remarks', $attendance->remarks ?? '') }}
                        </textarea>
                        <div class="form__error">
                            @error('remarks')
                                {{ $message }}
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