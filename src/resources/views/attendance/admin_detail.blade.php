@extends('layouts.default')

<!-- タイトル -->
@section('title', '勤怠一覧(管理者)')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_detail.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')
    <div class="admin-detail">
        <!-- タイトル -->
        <h1 class="admin-detail__title">
            勤怠詳細
        </h1>

        <!-- 勤務詳細 -->
        <form class="admin-detail__form" action="/attendance/detail/test" method="post" novalidate>
            @csrf
            <div class="admin-detail__card">
                <!-- 名前 -->
                <div class="admin-detail__row">
                    <dt class="admin-detail__term">名前</dt>
                    <dd class="admin-detail__data">{{ $attendance->user->name }}
                    </dd>
                </div>
                <!-- 日付 -->
                <div class="admin-detail__row">
                    <dt class="admin-detail__term">日付</dt>
                    <dd class="admin-detail__data">
                        <span class="admin-detail__data-year">
                            {{ $attendance->clock_in->isoFormat('Y年') }}
                        </span>
                        <span class="admin-detail__data-monthDay">
                            {{ $attendance->clock_in->isoFormat('M月D日') }}
                        </span>
                    </dd>
                </div>
                <!-- 出勤・退勤 -->
                <div class="admin-detail__row">
                    <dt class="admin-detail__term">出勤・退勤</dt>
                    <dd class="admin-detail__data admin-detail__data--column">
                        <div class="admin-detail__data-row">
                            <input class="admin-detail__time" type="text" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in->format('H:i')) }}" inputmode="numeric">
                            <span>～</span>
                            <input class="admin-detail__time" type="text" name="requested_clock_out" value="{{ old('requested_clock_out',$attendance->clock_out->format('H:i')) }}" inputmode="numeric">
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
                    <div class="admin-detail__row">
                        <dt class="admin-detail__term">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</dt>
                        <dd class="admin-detail__data admin-detail__data--column">
                            <div class="admin-detail__data-row">
                                <input class="admin-detail__time" type="text" name="requested_breaks[{{ $index }}][break_in]" value="{{ old('requested_breaks.' . $index . '.break_in',optional($break->break_in)->format('H:i')) }}" inputmode="numeric">
                                <span>～</span>
                                <input class="admin-detail__time" type="text" name="requested_breaks[{{ $index }}][break_out]" value="{{ old('requested_breaks.' . $index . '.break_out', optional($break->break_out)->format('H:i')) }}" inputmode="numeric">
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

                <div class="admin-detail__row">
                    <dt class="admin-detail__term">備考
                    </dt>
                    <dd class="admin-detail__data admin-detail__data--column">
                        <textarea class="admin-detail__remark" name="remarks">
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

            <div class="admin-detail__button">
                <button class="admin-detail__form-button">修正</button>
            </div>
        </form>
    </div>
@endsection