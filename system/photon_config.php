<?php

/*
 * Copyright (c) 2017, Entap,Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */

//
//	Photon -- a simple php library for building simple web applications
//	http://entap.github.com/photon
//

//------------------------------------------------------------------------------
// フォーム
//------------------------------------------------------------------------------

// 読込専用フォームで配列を結合する文字列
config('static_glue', ',');

// 読込専用フォームのパスワードの隠し文字
config('static_hidden', '*');

// 読込専用フォームの真の値の文字列
config('static_true', '[/]');

// 読込専用フォームの偽の値の文字列
config('static_false', '[&nbsp;]');

// 削除のチェックボックスのラベル
config('form_upload_remove', '削除');

// アップロード済みのテキスト
config('form_upload_link', 'ファイル');

// アップロード済みの基準URL
config('form_upload_dir', dirname(dirname(__FILE__)) . '/storage/');

// アップロード済みのテキスト
config('form_upload_url', '/storage/');

// form_dateの書式の{y}の選択肢の最小値
config('form_date_year_min', 2010);

// form_dateの書式の{y}の選択肢の最大値
config('form_date_year_max', date('Y') + 1);

// form_dateの書式の{p}の選択肢の最小値
config('form_date_past_min', 1900);

// form_dateのデフォルトの書式
config('form_date_format', '{y}年{m}月{d}日');

//------------------------------------------------------------------------------
// エラーメッセージ
//------------------------------------------------------------------------------

// アップロードに失敗した場合のエラーメッセージ
config('error_upload', 'アップロードに失敗しました');

// エラーメッセージの開始タグ
config('error_tag_open', '<div class="alert alert-danger">');

// エラーメッセージの終了タグ
config('error_tag_close', '</div>');

// 入力必須項目のエラーメッセージ
config('error_required', '入力必須項目です');

// 最小バイト数より短い場合のエラーメッセージ
config('error_min_bytes', '入力が短すぎます');

// 最大バイト数より長い場合のエラーメッセージ
config('error_max_bytes', '入力が長すぎます');

// 最小文字数より短い場合のエラーメッセージ
config('error_min_chars', '入力が短すぎます');

// 最大文字数より長い場合のエラーメッセージ
config('error_max_chars', '入力が長すぎます');

// 最小文字幅より短い場合のエラーメッセージ
config('error_min_width', '入力が短すぎます');

// 最大文字幅より長い場合のエラーメッセージ
config('error_max_width', '入力が長すぎます');

// 最小行数より短い場合のエラーメッセージ
config('error_min_lines', '行数が少ないです');

// 最大行数より長い場合のエラーメッセージ
config('error_max_lines', '行数が多いです');

// 最小値より小さい場合のエラーメッセージ
config('error_min_value', '入力値が小さすぎます');

// 最大値より大きい場合のエラーメッセージ
config('error_max_value', '入力値が大きすぎます');

// 入力が一致しない場合のエラーメッセージ
config('error_matches', '入力が一致しません');

// 選択肢から選択されていない場合のエラーメッセージ
config('error_options', '選択してください');

// 正規表現が一致しない場合のエラーメッセージ
config('error_preg', '正しく入力してください');

// アルファベットとして不正な場合のエラーメッセージ
config('error_alpha', '半角の英字で入力してください');

// 数字として不正な場合のエラーメッセージ
config('error_digit', '半角の数値で入力してください');

// 英数字として不正な場合のエラーメッセージ
config('error_alnum', '半角の英数字で入力してください');

// 英数字、アンダーバー("_")、ダッシュ("-")として不正な場合のエラーメッセージ
config('error_alnum_dash', '半角英数字、-か_で入力してください');

// 整数として不正な場合のエラーメッセージ
config('error_integer', '半角の数値で入力してください');

// 自然数として不正な場合のエラーメッセージ
config('error_natural', '半角の数値で入力してください');

// 小数値として不正な場合のエラーメッセージ
config('error_decimal', '半角の数値で入力してください');

// メールアドレスとして不正な場合のエラーメッセージ
config('error_mail', '半角のメールアドレスを正しく入力してください');

// IPv4アドレスとして不正な場合のエラーメッセージ
config('error_ipv4', 'IPアドレスを正しく入力してください');

// ひらがなではない場合のエラーメッセージ
config('error_hiragana', 'ひらがなを入力してください');

// カタカナではない場合のエラーメッセージ
config('error_katakana', 'カタカナを入力してください');

// URLのエラーメッセージ
config('error_url', 'URLを正しく入力してください');

// データベースの接続に失敗した場合のエラーメッセージ
config('error_db_connect', 'データベースの接続に失敗しました');

// データベースの選択に失敗した場合のエラーメッセージ
config('error_db_select', 'データベースの選択に失敗しました');

// 文字コードの選択に失敗した場合のエラーメッセージ
config('error_db_charset', '文字コードの設定に失敗しました');

// クエリに失敗した場合のエラーメッセージ
config('error_db_query', 'クエリに失敗しました');

// プライマリキーの取得に失敗した場合のエラーメッセージ
config('error_primary_key', 'プライマリキーが取得できませんでした');

// 条件指定の連想配列が空の場合のエラーメッセージ
config('error_invalid_condition', '条件の指定が不正です');

// 致命的なエラーのタイトル
config('error_title', 'エラーが発生しました');

// アクションが見つからなかった場合のエラーメッセージ
config('error_action_not_found', 'アクションが見つかりませんでした');

// ビューファイルが見つからなかった場合のエラーメッセージ
config('error_view_not_found', 'ビューファイルが見つかりませんでした');

//------------------------------------------------------------------------------
// ページネーション
//------------------------------------------------------------------------------

// ページ毎のデータ数
config('paginate_per_page', 30);

// 表示するページ番号の数
config('paginate_num_links', 5);

// 最初に移動するタグ
config('paginate_first_tag', '<li><a href="{url}">&laquo;</a></li>');

// 前に移動するタグ
config('paginate_prev_tag', '<li><a href="{url}">&lt;</a></li>');

// ページ番号に移動するタグ
config('paginate_link_tag', '<li><a href="{url}">{page}</a></li>');

// 現在のページのタグ
config('paginate_active_tag', '<li class="active"><a href="{url}">{page}</a></li>');

// 次に移動するタグ
config('paginate_next_tag', '<li><a href="{url}">&gt;</a></li>');

// 最後に移動するタグ
config('paginate_last_tag', '<li><a href="{url}">&raquo;</a></li>');

// ページネーションの開始タグ
config('paginate_open_tag', '<nav><ul class="pagination">');

// ページネーションの終了タグ
config('paginate_close_tag', '</ul></nav>');

//------------------------------------------------------------------------------
// 認証
//------------------------------------------------------------------------------

// 認証の有効期限(秒数)
config('auth_expire', 3600);

// 認証の名前
config('auth_realm', 'auth');

//------------------------------------------------------------------------------
// ログ
//------------------------------------------------------------------------------

// メール送信履歴のテーブル名(空文字列で無効化)
config('log_mail', 'log_mail');

// エラー履歴のテーブル名(空文字列で無効化)
config('log_error', 'log_error');

// クエリ履歴のテーブル名(空文字列で無効化)
config('log_query', '');

//------------------------------------------------------------------------------
// データベース
//------------------------------------------------------------------------------
// データベースの接続文字コード
config('db_charset', 'utf8');

//------------------------------------------------------------------------------
// ビュー
//------------------------------------------------------------------------------
// ビューの相対位置
config('view_dir', './');
?>
