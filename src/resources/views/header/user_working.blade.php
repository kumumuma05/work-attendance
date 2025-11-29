<header class="header">
    <div class="header__logo">
        <a href="/">
            <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="ロゴ">
        </a>
    </div>

    <div class="header__nav">
        <a class="header__link" href="/attendance">勤怠</a>
        <a class="header__link" href="attendance/list">勤怠一覧</a>
        <a class="header__link" href="">申請</a>
        <form class="logout" action="/logout" method="post">
            @csrf
            <button class="logout__button" type="submit">ログアウト</button>
        </form>
    </div>
</header>