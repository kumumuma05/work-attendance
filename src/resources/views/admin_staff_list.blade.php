@extends('layouts.default')

<!-- タイトル -->
@section('title', 'スタッフ一覧')

<!-- CSS -->
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

<!-- 本体 -->
@section('content')
    @include('header.admin')

    <div class="staff-list">
        <!-- タイトル -->
        <h1 class="staff-list__title">
            スタッフ一覧
        </h1>

        <!-- テーブル -->
        <div class="staff-list__table-inner">
            <table class="staff-list__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a  class="staff-list__detail-link" href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection



