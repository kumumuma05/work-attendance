@extends('layouts.default')

<!-- タイトル -->
@section('title', '申請一覧')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.user_working')
        <div class="correction-request">
            <!-- タイトル -->
            <h1 class="correction-request__title">
                申請一覧
            </h1>

            <!-- タブ -->
            <div class="correction-request__tab">
                <ul class="correction-request__tab-list">
                    <li class="correction-request__tab-item">
                        <a class="correction-request__tab-link {{ $tab === 'pending' ? 'is-active' : '' }}" href="?tab=pending">承認待ち</a>
                    </li>
                    <li class="correction-request__tab-item">
                        <a class="correction-request__tab-link {{ $tab === 'approved' ? 'is-active' : '' }}" href="?tab=approved">承認済み</a>
                    </li>
                </ul>
            </div>

            <!-- テーブル -->
            <div class="correction-request__table-inner">
                <table class="correction-request__table">
                    <colgroup>
                        <col style="width: 140px;">
                        <col style="width: 150px;">  <!-- 名前 -->
                        <col style="width: 160px;">  <!-- 対象日 -->
                        <col style="width: auto;">   <!-- 申請理由 -->
                        <col style="width: 160px;">  <!-- 申請日 -->
                        <col style="width: 130px;">   <!-- 詳細 -->
                    </colgroup>
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($requests as $correction)
                            <tr>
                                <td>
                                    <span class="correction-request__status">
                                        {{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}
                                    </span>
                                </td>
                                <td>{{ $correction->attendance->user->name }}</td>
                                <td>{{ $correction->attendance->clock_in->isoFormat('YYYY/MM/DD') }}</td>
                                <td>{{ $correction->remarks }}</td>
                                <td>{{ $correction->created_at->isoFormat('YYYY/MM/DD') }}</td>
                                <td>
                                    <a class="correction-request__link" href="/attendance/detail/{{ $correction->attendance->id }}">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endsection