<?php
/*
 * 定数定義ファイル
 * Controller、Modelで共通的に使用する定数を定義する。
 */

return [

    /* ユーザ権限 */
    'user_auth' => [
        'coach' => 1,       // コーチ権限
        'back_office' => 2,  // バックオフィス権限
        'admin' => 3,       // 管理者権限
        'owner' => 4,       // オーナー権限
    ],

    /* フロントエンドのURL */
    'frontend' => [
        // 本番環境
        'product'       => 'https://coachtech-crm.com',
        // ローカル環境
        'local'         => 'http://localhost:3000',
        // パスワードリセットページURL
        'reset_pass'     => 'password/reset?token=',
        // アクティベーションページURL
        'activation'    => '/user/activate?token=',

    ],

    /* 入力チェック用正規表現 */
    'regex' => [
        // メールアドレス形式チェック
        'email' => '{^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)@([a-zA-Z0-9][a-zA-Z0-9-][a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$}u',
        // パスワード
        'password' => [
            // 無効文字チェック
            'invalid' => '{^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!#%&( )+,-./;<=>?@\[\]^_{|}~])[a-zA-Z0-9!#%&( )+,-./;<=>?@\[\]^_{|}~]$}u',
            // フォーマットチェック
            'format' => '{^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!#%&( )+,-./;<=>?@\[\]^_{|}~])[a-zA-Z0-9!#%&( )+,-./;<=>?@\[\]^_{|}~]+$}u',
        ],
        // 数字チェック
        'number' => '{^[0-9]+$}u',
        // UUID形式チェック
        'uuid' => '{^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$}u',
    ],

    /* ユーザ認証トークンの有効時間(分) */
    'token_valide_minute' => 60,

];
