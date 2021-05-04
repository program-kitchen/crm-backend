<?php
/*
 * 定数定義ファイル
 * Controller、Modelで共通的に使用する定数を定義する。
 */

return [

    /* ユーザ権限 */
    'userAuth' => [
        'coach' => 1,       // コーチ権限
        'backOffice' => 2,  // バックオフィス権限
        'admin' => 3,       // 管理者権限
        'owner' => 4,       // オーナー権限
    ],

    /* フロント側TOPページ */
    'frontTop' => '/',

];
