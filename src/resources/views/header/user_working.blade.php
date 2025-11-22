<header class="header">
    <div class="header__logo">
        <a href="/">
            <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="ロゴ">
        </a>
    </div>

    <div class="header__nav">
        <a href="">勤怠</a>
        <a href="">勤務一覧</a>
        <form class="logout" action="/logout" method="post">
            @csrf
            <button class="logout__button" type="submit">ログアウト</button>
        </form>
    </div>
</header>