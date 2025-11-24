@extends('layouts.default')

<!-- タイトル -->
@section('title', 'メール認証')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.guest')

    <!-- セッションメッセージ -->
    @if(session('status') === 'verification-link-sent')
        <div class="session__alert">
            認証メールを再送しました。
        </div>
    @endif

    <div class="verify-email">

        <p class="verify-email__text">登録していただいたメールアドレスに承認メールを送付しました。</p>
        <p class="verify-email__text">メール認証を完了してください。</p>

        <div class="verify-email__link">
            <a class="verify-email__link-button" href="http://localhost:8025">認証はこちらから</a>
        </div>

        <form class="verify-email__form" method="post" action="/email/verification-notification">
            @csrf
            <button class="verify-email__form-button" type="submit">認証メールを再送する</button>
        </form>
    </div>

@endsection