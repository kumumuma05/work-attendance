<header class="header">
    <div class="header__logo">
        <a href="/attendance">
            <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="ロゴ">
        </a>
    </div>

    <div class="header__nav">
        <a class="header__link header__link--off" href="/attendance/list">今月の出勤一覧</a>
        <a class="header__link header__link--off" href="/stamp_correction_request/list">申請一覧</a>
        <form class="logout" action="/logout" method="post">
            @csrf
            <button class="logout__button" type="submit">ログアウト</button>
        </form>
    </div>
</header>