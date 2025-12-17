<header class="header">
    <div class="header__logo">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="ロゴ">
    </div>

    <div class="header__nav">
        <a class="header__link" href="/admin/attendance/list">勤怠一覧</a>
        <a class="header__link" href="/admin/staff/list">スタッフ一覧</a>
        <a class="header__link" href="/stamp_correction_request/list">申請一覧</a>
        <form class="logout" action="/admin/logout" method="post">
            @csrf
            <button class="logout__button" type="submit">ログアウト</button>
        </form>
    </div>
</header>