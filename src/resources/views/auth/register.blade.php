@extends('layouts.default')

<!-- タイトル -->
@section('title', '会員登録')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.guest')
    <form class="register-form" action="/register" method="post" novalidate>
        @csrf
        <!-- 見出し -->
        <h1 class="page__title">会員登録</h1>

        <!-- 名前 -->
        <label class="form__label" for="name">名前</label>
        <input class="form__input register__input" type="text" id="name" name="name" value="{{ old('name') }}">
        <div class="form__error">
            @error('name')
                {{ $message }}
            @enderror
        </div>

        <!-- メールアドレス -->
        <label class="form__label" for="email">メールアドレス</label>
        <input class="form__input register__input" type="email" id="email" name="email" value="{{ old('email') }}">
        <div class="form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>

        <!-- パスワード -->
        <label class="form__label" for="password">パスワード</label>
        <input class="form__input register__input" type="password" id="password" name="password">
        <div class="form__error">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <!-- 確認用パスワード -->
        <label class="form__label" for="password_confirm">パスワード確認</label>
        <input class="form__input register__input" type="password" id="password_confirm" name="password_confirm">
        <div class="form__error">
            @error('password_confirm')
                {{ $message }}
            @enderror
        </div>

        <!-- 送信ボタン -->
        <button class="form__button register__button" type="submit">登録する</button>

        <!-- リンク -->
        <a class="link login-link" href="/login">ログインはこちら</a>
    </form>

@endsection

