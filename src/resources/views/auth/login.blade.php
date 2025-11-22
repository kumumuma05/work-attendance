@extends('layouts.default')

<!-- タイトル -->
@section('title', 'ログイン')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.guest')
    <form class="login-form" action="/login" method="post" novalidate>
        @csrf
        <!-- 見出し -->
        <h1 class="page__title">ログイン</h1>

        <!-- メールアドレス -->
        <label class="form__label" for="email">メールアドレス</label>
        <input class="form__input login__input" type="email" id="email" name="email" value="{{ old('email') }}">
        <div class="form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>

        <!-- パスワード -->
        <label class="form__label" for="password">パスワード</label>
        <input class="form__input login__input" type="password" id="password" name="password">
        <div class="form__error">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <!-- 送信ボタン -->
        <button class="form__button login__button" type="submit">ログインする</button>

        <!-- リンク -->
        <a class="link login-link" href="/register">会員登録はこちら</a>
    </form>

@endsection

