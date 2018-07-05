<?php

/*
 * Copyright (c) 2014-2017, Entap,Inc.
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

require_once dirname(__FILE__) . '/photon_config.php';
require_once dirname(__FILE__) . '/config.php';

//------------------------------------------------------------------------------
// 基本関数
//------------------------------------------------------------------------------

/**
 * 入力に対するデフォルト値の処理を行う
 *
 * $valueが空文字列・NULLでない場合には$valueを返す。
 * そうでない場合にはデフォルト値を返す。
 *
 * 複数引数を指定した場合、空文字列・NULLではない先頭の引数の値を返す。
 *
 * @param	string	$value		入力値
 * @param	string	$default	デフォルト値
 * @return	string	デフォルト値の処理の結果
 * @package	basic
 */
function default_value($value, $default)
{
	foreach (func_get_args() as $arg) {
		if (!($arg === '' || $arg === NULL)) {
			return $arg;
		}
	}
	return NULL;
}

/**
 * 入力値の値の範囲を制限する
 *
 * $valueが$minより小さい場合には$minを返す。
 * $valueが$maxより大きい場合には$maxを返す。
 * $valueが$min以上$max以下の範囲内の場合には$valueを返す。
 *
 * @param	integer	$value	入力値
 * @param	integer	$min	下限値
 * @param	integer	$max	上限値
 * @return	integer	入力値の値の範囲を制限した結果
 * @package	basic
 */
function limit_range($value, $min, $max)
{
	if ($value < $min) {
		return $min;
	} elseif ($value > $max) {
		return $max;
	} else {
		return $value;
	}
}

/**
 * 節の処理を行う
 *
 * $bodyが空文字列・NULLでない場合には、
 * $prefix.$body.$suffixを連結した文字列を返す。
 * そうでない場合には空文字列を返す。
 *
 * @param	string	$body	入力文字列
 * @param	string	$prefix	接頭辞
 * @param	string	$suffix	接尾辞
 * @return	string	節の処理の結果
 * @package	basic
 */
function clause($body, $prefix, $suffix)
{
	if ($body === '' || $body === NULL) {
		return '';
	} else {
		return $prefix . $body . $suffix;
	}
}

/**
 * 多次元連想配列の要素をパス形式で指定して、値を設定する
 *
 * パス形式とは、要素を辿るためのキーを並べた形式である。
 * 例えば、下記はいずれも$array['key1']['key2']['key3']の指定となる。
 * - array('key1', 'key2', 'key3')
 * - 'key1[key2][key3]'
 * - 'key1.key2.key3'
 *
 * @param	array	$array	対象の多次元連想配列
 * @param	mixed	$name	要素を指定する文字列
 * @param	mixed	$value	設定する値
 * @package	basic
 */
function array_set(&$array, $name, $value)
{
	if (!is_array($name)) {
		$name = explode('.', str_replace(array('][', '[', ']'), '.', $name));
	}
	$ptr = &$array;
	foreach ($name as $key) {
		if ($key === '') {
			continue;
		}
		if (!is_array($ptr)) {
			$ptr = array();
		}
		if (!isset($ptr[$key])) {
			$ptr[$key] = array();
		}
		$ptr = &$ptr[$key];
	}
	$ptr = $value;
}

/**
 * 多次元連想配列の要素をパス形式で指定して、値を取得する
 *
 * パス形式とは、要素を辿るためのキーを並べた形式である。
 * 例えば、下記はいずれも$array['key1']['key2']['key3']の指定となる。
 * - array('key1', 'key2', 'key3')
 * - 'key1[key2][key3]'
 * - 'key1.key2.key3'
 *
 * @param	array	$array	対象の多次元連想配列
 * @param	mixed	$name	要素を指定する文字列
 * @return	mixed	取得した値
 * @package	basic
 */
function array_get(&$array, $name)
{
	if (!is_array($name)) {
		$name = explode('.', str_replace(array('][', '[', ']'), '.', $name));
	}
	$ptr = &$array;
	foreach ($name as $key) {
		if ($key === '') {
			continue;
		}
		if (!is_array($ptr)) {
			return NULL;
		}
		if (!isset($ptr[$key])) {
			return NULL;
		}
		$ptr = &$ptr[$key];
	}
	return $ptr;
}

/**
 * 相対パスを解決する
 *
 * $baseを基準パスとして、$relativeの相対パス指定を解決する。
 * 相対パス指定として、親ディレクトリ..や現在ディレクトリ.が使用できる。
 *
 * $relativeが/で始まる場合、絶対パスが指定されたとみなし、
 * $baseは無視される。
 *
 * $baseにファイル名が指定された場合、ファイルの親ディレクトリが基準パスとなる。
 *
 * @param	string	$relative	相対パス
 * @param	string	$base		基準パス
 * @return	string	相対パスを解決した結果
 * @package	basic
 */
function relative_path($relative, $base = '')
{
	// 処理対象のパスを求める
	if (substr($base, -1) != '/') {
		$base = dirname($base);
	}
	if (substr($relative, 0, 1) == '/') {
		$path = $relative;
	} else {
		$path = $base . '/' . $relative;
	}

	// 相対パスを解決する
	$dirs = explode('/', $path);
	$stack = array();
	$depth = 0;
	foreach ($dirs as $dir) {
		if ($dir === '' || $dir === '.') {
			continue;
		} elseif ($dir === '..') {
			if (count($stack) == 0) {
				$depth++;
			} else {
				array_pop($stack);
			}
		} else {
			array_push($stack, $dir);
		}
	}

	// 結果のパスを生成
	if (substr($path, 0, 1) == '/') {
		return '/' . implode('/', $stack);
	} else {
		return str_repeat('../', $depth) . implode('/', $stack);
	}
}

/**
 * URLの構成要素からURLを表す文字列を生成する
 *
 * @param	array	$url_parsed	URLの構成要素
 * @return	string	URLを表す文字列
 * @package	basic
 */
function build_url($url_parsed)
{
	if (!is_array($url_parsed)) {
		return $url_parsed;
	}

	// スキーム
	if (isset($url_parsed['scheme'])) {
		if (strcasecmp($url_parsed['scheme'], 'mailto') == 0) {
			$url = $url_parsed['scheme'] . ':';
		} else {
			$url = $url_parsed['scheme'] . '://';
		}
	} else {
		$url = '';
	}

	// ユーザ名・パスワード
	if (isset($url_parsed['user'])) {
		$url .= $url_parsed['user'];
		if (isset($url_parsed['pass'])) {
			$url .= ':' . $url_parsed['pass'];
		}
		$url .= '@';
	}

	// ホスト名
	if (isset($url_parsed['host'])) {
		$url .= $url_parsed['host'];
		if (isset($url_parsed['port'])) {
			$url .= ':' . $url_parsed['port'];
		}
	}

	// パス
	$url .= '/';
	if (isset($url_parsed['path'])) {
		$url .= ltrim($url_parsed['path'], '/');
	}

	// クエリ
	if (isset($url_parsed['query'])) {
		$url .= '?' . $url_parsed['query'];
	}

	// フラグメント
	if (isset($url_parsed['fragment'])) {
		$url .= '#' . $url_parsed['fragment'];
	}

	return $url;
}

/**
 * URLのクエリ文字列を連想配列から生成する
 *
 * @param	array	$data	URLのクエリを表す連想配列
 * @param	string	$prefix	クエリデータの接頭辞
 * @return	string	URLのクエリ文字列
 * @package	basic
 */
function build_url_query($data, $prefix = NULL)
{
	$query = '';
	foreach ($data as $key => $value) {
		if ($prefix === NULL) {
			$name = urlencode($key);
		} else {
			$name = $prefix . '[' . urlencode($key) . ']';
		}
		if (is_array($value)) {
			$query .= build_url_query($value, $name) . '&';
		} else {
			$query .= $name . '=' . urlencode($value) . '&';
		}
	}
	return substr($query, 0, -1);
}

/**
 * 相対URLを解決する
 *
 * $baseを基準URLとして、$relativeの相対URL指定を解決する。
 * $relativeにはparse_urlでパースできるURLが使用できる。
 *
 * @param	string	$relative	相対URL
 * @param	string	$base		基準URL
 * @return	string	相対URLを解決した結果
 * @package	basic
 */
function relative_url($relative, $base)
{
	$r = parse_url($relative);
	$b = parse_url($base);
	if (isset($r['scheme'])) {
		// $relativeにスキームが指定された場合
		return $relative;
	} elseif (isset($r['host'])) {
		// $relativeにホスト名が指定された場合
		$u = $r;
		$u['scheme'] = $b['scheme'];
	} elseif (isset($r['path'])) {
		// $relativeにパスが指定された場合
		$u = $b;
		$u['path'] = relative_path($r['path'], $b['path']);
		if (isset($r['query'])) {
			$u['query'] = $r['query'];
		} else {
			unset($u['query']);
		}
		if (isset($r['fragment'])) {
			$u['fragment'] = $r['fragment'];
		} else {
			unset($u['fragment']);
		}
	} elseif (isset($r['query'])) {
		// $relativeにクエリが指定された場合
		$u = $b;
		$u['query'] = $r['query'];
		if (isset($r['fragment'])) {
			$u['fragment'] = $r['fragment'];
		} else {
			unset($u['fragment']);
		}
	} elseif (isset($r['fragment'])) {
		// $relativeにフラグメントが指定された場合
		$u = $b;
		$u['fragment'] = $r['fragment'];
	} else {
		return $base;
	}
	return build_url($u);
}

/**
 * URLのクエリをマージする
 *
 * @param	string	$url	元のURL
 * @param	mixed	$query	マージするクエリの文字列、または連想配列
 * @return	string	生成したURLを表す文字列
 * @package	basic
 */
function modify_url_query($url, $query)
{
	// URLをパース
	if (!is_array($url)) {
		$url = parse_url($url);
	}

	// クエリをマージ
	if (isset($url['query'])) {
		parse_str($url['query'], $array1);
	} else {
		$array1 = array();
	}
	if (is_array($query)) {
		$array2 = $query;
	} else {
		parse_str($query, $array2);
	}
	$url['query'] = build_url_query(array_merge($array1, $array2));

	// URLを構築
	return build_url($url);
}

/**
 * 現在のリクエストURLを取得する
 *
 * @return	string	現在のリクエストURLを表す文字列
 * @package	basic
 */
function get_request_url()
{
	// スキーム
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
		$url = 'https://';
		$port = 443;
	} else {
		$url = 'http://';
		$port = 80;
	}

	// ホスト名
	if (isset($_SERVER['HTTP_HOST'])) {
		$url .= strtok($_SERVER['HTTP_HOST'], ':');
	} elseif (isset($_SERVER['SERVER_NAME'])) {
		$url .= $_SERVER['SERVER_NAME'];
	} elseif (isset($_SERVER['SERVER_ADDR'])) {
		$url .= $_SERVER['SERVER_ADDR'];
	}

	// ポート番号
	if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != $port) {
		$url .= ':' . $_SERVER['SERVER_PORT'];
	}

	// パス
	$url .= '/';
	if (isset($_SERVER['REQUEST_URI'])) {
		$url .= ltrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
	} elseif (isset($_SERVER['PHP_SELF'])) {
		$url .= ltrim(htmlspecialchars($_SERVER['PHP_SELF']), '/');
	}

	// クエリ
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
		$url .= '?' . $_SERVER['QUERY_STRING'];
	}

	return $url;
}

/**
 * 現在のリクエストがPOSTかを調べる
 *
 * @return	boolean	現在のリクエストがPOSTならTRUE、そうでない場合にはFALSE
 * @package	basic
 */
function is_request_post()
{
	return strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0 ? TRUE : FALSE;
}

/**
 * 変数の真偽値を調べる
 *
 * $varが真偽値のTRUE、または文字列のyes、y、true、tである場合にはTRUE、
 * そうでない場合にはFALSEを返す。
 *
 * @param	mixed	$var	対象の変数
 * @return	boolean	$varの真偽値
 * @package	basic
 */
function is_true($var)
{
	if (is_numeric($var)) {
		return intval($var) ? TRUE : FALSE;
	} else if (is_string($var)) {
		$char = substr($var, 0, 1);
		return $char == 'y' || $char == 't' || $char == 'on' ? TRUE : FALSE;
	} else {
		return $var ? TRUE : FALSE;
	}
}

/**
 * 文字列がメールアドレスとして正しいか調べる
 *
 * @param	string	$str	対象の文字列
 * @return	boolean	文字列がメールアドレスとして正しい場合にはTRUE。
 * 					そうでない場合にはFALSE
 * @package	basic
 */
function is_mail($str)
{
	return preg_match('/^[a-z0-9._+^~-]+@[a-z0-9.-]+$/i', $str) ? TRUE : FALSE;
}

/**
 * 文字列がIPv4アドレスとして正しいか調べる
 *
 * @param	string	$str	対象の文字列
 * @return	boolean	文字列がIPv4アドレスとして正しい場合にはTRUE。
 * 					そうでない場合にはFALSE
 * @package	basic
 */
function is_ipv4($str)
{
	return long2ip(ip2long($str)) === $str ? TRUE : FALSE;
}

/**
 * 文字列がひらがなか調べる
 *
 * @param	string	$str	対象の文字列
 * @return	boolean	ひらがなならTRUE。そうでない場合にはFALSE
 * @package	basic
 */
function is_hiragana($str)
{
	return preg_match("/^[ぁ-ん]+$/u", $str);
}

/**
 * 文字列がカタカナか調べる
 *
 * @param	string	$str	対象の文字列
 * @return	boolean	ひらがなならTRUE。そうでない場合にはFALSE
 * @package	basic
 */
function is_katakana($str)
{
	return preg_match("/^[ァ-ヶー]+$/u", $str);
}

/**
 * テンプレート文字列に変数を埋め込む
 *
 * テンプレート文字列には{key1.key2}の形式で変数を指定でき、
 * $vars[$key1][$key]の値で置換される。
 * $filterを指定した場合、$filterが変換関数として使用される。
 *
 * @param	string	$template	テンプレート文字列
 * @param	array	$vars		変数の連想配列
 * @param	string	$filter		フィルタ関数
 * @return	string	テンプレート文字列に変数を埋め込んだ結果
 * @package	basic
 */
function embed($template, $vars, $filter = NULL)
{
	global $__photon_vars;
	global $__photon_filter;
	$__photon_vars = $vars;
	$__photon_filter = $filter;
	return preg_replace_callback('/{([a-zA-Z0-9._]+)}/', '__embed', $template);
}

/** @ignore */
function __embed($matches)
{
	global $__photon_vars;
	global $__photon_filter;
	$value = array_get($__photon_vars, $matches[1]);
	if ($__photon_filter === NULL) {
		return $value;
	} else {
		return call_user_func($__photon_filter, $value);
	}
}

/**
 * 文字列の改行コードを置換する
 *
 * @param	string	$str		対象の文字列
 * @param	string	$newline	改行コード
 * @return	string	改行コードを置換した結果
 * @package	basic
 */
function convert_newline($str, $newline = "\n")
{
	$str = str_replace(array("\r\n", "\r", "\n"), "\n", $str);
	if ($newline == "\n") {
		return $str;
	} else {
		return str_replace("\n", $newline, $str);
	}
}

/**
 * ファイルの拡張子を取得する
 *
 * @param	string	$filename	ファイル名
 * @return	string	ファイルの拡張子
 * @package	basic
 */
function extension($filename)
{
	$str = strrchr($filename, '.');
	if ($str === FALSE) {
		return NULL;
	} else {
		return substr($str, 1);
	}
}

/**
 * アンダースコアで区切った文字列をキャメルケースの文字列に変換する
 *
 * @param	string	$str		アンダースコアで区切った文字列
 * @param	string	$lcfirst	先頭の文字を小文字にするか？
 * @return	string	キャメルケースの文字列
 * @package	basic
 */
function camelize($str, $lcfirst = true)
{
	$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
	if ($lcfirst) {
		$str = lcfirst($str);
	}
	return $str;
}

/**
 * キャメルケースの文字列をアンダースコアで区切った文字列に変換する
 *
 * @param	string	$str	キャメルケースの文字列
 * @return	string	アンダースコアで区切った文字列
 * @package	basic
 */
function underscore($str)
{
	return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $str));
}

/**
 * 配列の全要素のキーに対して、ユーザ関数を適用する
 *
 * @param	array		$array		対象の配列
 * @param	callable	$callback	キーに対して適用するユーザ関数
 * @param	mixed		$userdata	ユーザ関数の引数
 * @return	array		変換結果の配列
 * @package	basic
 */
function array_change_key($array, $callback, $userdata = NULL)
{
	$result = array();
	foreach ($array as $key => $value) {
		$key = call_user_func($callback, $key, $userdata);
		if (is_array($value)) {
			$result[$key] = array_change_key($value, $callback, $userdata);
		} else {
			$result[$key] = $value;
		}
	}
	return $result;
}

/**
 * 乱数文字列を生成する
 *
 * @param	integer	$n	文字数
 * @return	string	乱数文字列
 * @package	basic
 */
function random_str($n)
{
	$str = base64_encode(openssl_random_pseudo_bytes(ceil($n * 6 / 8)));
	$str = str_replace(array('+', '/'), array('-', '_'), $str);
	$str = substr($str, 0, $n);
	return $str;
}

/**
 * 配列変数をCSVに変換する
 *
 * @param    array  $data   配列変数
 * @param    array  $keys   出力するキーの配列
 *
 * @return    string    CSV
 * @package    basic
 */
function array_to_csv($data, $keys)
{
	$fp = fopen('php://temp', 'w');
	fputcsv($fp, $keys);
	foreach ($data as $row) {
		$fields = [];
		foreach ($keys as $key) {
			$fields[$key] = isset($row[$key]) ? strval($row[$key]) : '';
		}
		fputcsv($fp, $fields);
	}
	rewind($fp);
	$str = stream_get_contents($fp);
	fclose($fp);
	return pack('C*', 0xef, 0xbb, 0xbf) . $str;
}

/**
 * CSVの文字列を配列変数に変換する
 *
 * @param    string $csv    CSVの文字列
 * @param    array  $keys   出力するキーの配列
 *
 * @return    array    配列変数
 * @package    basic
 */
function csv_to_array($csv, $keys)
{
	$encoding = mb_detect_encoding($csv, ['utf-8', 'utf-16', 'sjis-win']);
	if (ord($csv[0]) == 0xef && ord($csv[1]) == 0xbb && ord($csv[2]) == 0xbf) {
		$csv = substr($csv, 3);
	}
	$csv = mb_convert_encoding($csv, 'utf-8', $encoding);
	$fp = fopen('php://temp', 'w');
	fwrite($fp, $csv);
	rewind($fp);
	if (fgetcsv($fp) != $keys) {
		return FALSE; // 失敗
	}
	while ($fields = fgetcsv($fp)) {
		$row = [];
		foreach ($keys as $i => $key) {
			$row[$key] = $fields[$i];
		}
		$data[] = $row;
	}
	fclose($fp);
	return $data;
}

/**
 * 設定オプションの値を設定する
 *
 * 引数を一つだけ指定した場合には、設定オプションの値を取得する
 *
 * @param	string	$name	設定オプションの名前
 * @param	string	$value	設定オプションの値
 * @return	string	取得した設定オプションの値
 * @package	basic
 */
function config($name, $value = NULL)
{
	global $__photon_config;
	if (func_num_args() == 1) {
		if (isset($__photon_config[$name])) {
			return $__photon_config[$name];
		} else {
			return NULL;
		}
	} else {
		return $__photon_config[$name] = $value;
	}
}

//------------------------------------------------------------------------------
// HTMLタグ
//------------------------------------------------------------------------------

/**
 * HTML開始タグを生成する
 *
 * @param	string	$name		タグの名前
 * @param	mixed	$attributes	タグの属性の文字列、または連想配列
 * @param	boolean	$is_empty	空タグの場合にはTRUE、そうでない場合にはFALSE
 * @return	string	生成した開始タグ
 * @package	html
 */
function tag_open($name, $attributes = NULL, $is_empty = FALSE)
{
	$attributes = tag_build_attributes($attributes);
	$str = '<' . htmlspecialchars($name);
	if ($attributes !== '') {
		$str .= ' ' . $attributes;
	}
	if ($is_empty) {
		return $str . ' />';
	} else {
		return $str . '>';
	}
}

/**
 * HTML終了タグを生成する
 *
 * @param	string	$name		タグの名前
 * @return	string	生成した終了タグ
 * @package	html
 */
function tag_close($name)
{
	return '</' . htmlspecialchars($name) . '>';
}

/**
 * HTMLタグの属性文字列をパースする
 *
 * 例えば、$strに文字列'key1="value1" key2="value2"'を指定した場合、
 * array('key1'=>'value1', 'key2'=>'value2')を返す。
 *
 * @param	string	$str	タグの属性の文字列
 * @return	array	タグの属性の連想配列
 * @package	html
 */
function tag_parse_attributes($str)
{
	if (is_array($str)) {
		return $str;
	}
	if ($str === NULL) {
		return array();
	}
	$array = array();
	$strlen = strlen($str);
	$spaces = " \t\r\n";
	$i = 0;
	while ($i < $strlen) {
		$i += strspn($str, $spaces, $i);
		$n = strcspn($str, $spaces . '=', $i);
		$key = substr($str, $i, $n);
		$i += $n;
		$i += strspn($str, $spaces, $i);
		if (substr($str, $i, 1) == '=') {
			$i++;
			$i += strspn($str, $spaces, $i);
			if (substr($str, $i, 1) == '"') {
				$i++;
				$n = strcspn($str, '"', $i);
				$value = substr($str, $i, $n);
				$i += $n + 1;
			} else {
				$n = strcspn($str, $spaces, $i);
				$value = substr($str, $i, $n);
				$i += $n;
			}
		} else {
			$value = $key;
		}
		$array[$key] = $value;
	}
	return $array;
}

/**
 * HTMLタグの属性文字列を生成する
 *
 * 例えば、$arrayに連想配列array('key1'=>'value1', 'key2'=>'value2')を
 * 指定した場合、文字列'key1="value1" key2="value2"'を返す。
 *
 * @param	array	$array	タグの属性の連想配列
 * @return	string	タグの属性の文字列
 * @package	html
 */
function tag_build_attributes($array)
{
	if (is_string($array)) {
		return $array;
	}
	if ($array === NULL) {
		return '';
	}
	$str = '';
	foreach ($array as $key => $value) {
		if (is_string($value) || is_numeric($value)) {
			$str .= htmlspecialchars($key) . '=';
			$str .= '"' . htmlspecialchars($value) . '" ';
		}
	}
	return rtrim($str, ' ');
}

/**
 * &lt;input&gt;タグを生成する
 *
 * @param	string	$type		type属性の値
 * @param	string	$name		name属性の値
 * @param	string	$value		value属性の値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_input($type, $name, $value, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['type'])) {
		$attributes['type'] = $type;
	}
	if (!isset($attributes['name'])) {
		$attributes['name'] = $name;
	}
	if (!isset($attributes['value'])) {
		$attributes['value'] = $value;
	}
	return tag_open('input', $attributes, TRUE);
}

/**
 * &lt;input type="hidden"&gt;タグを生成する
 *
 * @param	string	$data	出力するデータを表す連想配列
 * @param	string	$prefix	フォームの名前の接頭辞
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_hidden($data, $prefix = NULL)
{
	if ($data === NULL) {
		return '';
	}
	$str = '';
	foreach ($data as $key => $value) {
		if ($prefix === NULL) {
			$name = $key;
		} else {
			$name = $prefix . '[' . $key . ']';
		}
		if (is_array($value)) {
			$str .= tag_hidden($value, $name);
		} else {
			$str .= tag_input('hidden', $name, $value);
		}
	}
	return $str;
}

/**
 * &lt;input type="radio"&gt;タグを生成する
 *
 * @param	string	$name		name属性の値
 * @param	string	$value		value属性の値
 * @param	string	$label		ラベル文字列
 * @param	string	$checked	選択されている場合はTRUE、そうでない場合はFALSE
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_radio($name, $value, $label, $checked, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['type'])) {
		$attributes['type'] = 'radio';
	}
	if (!isset($attributes['name'])) {
		$attributes['name'] = $name;
	}
	if (!isset($attributes['value'])) {
		$attributes['value'] = $value;
	}
	if (!isset($attributes['checked']) && $checked) {
		$attributes['checked'] = 'checked';
	}
	if ($label === '' || $label === NULL) {
		return tag_open('input', $attributes, TRUE);
	} else {
		if (isset($attributes['label'])) {
			$label_attributes = $attributes['label'];
		} else {
			$label_attributes = NULL;
		}
		$str = tag_open('label', $label_attributes);
		$str .= tag_open('input', $attributes, TRUE);
		$str .= htmlspecialchars($label) . tag_close('label');
		return $str;
	}
}

/**
 * &lt;input type="checkbox"&gt;タグを生成する
 *
 * @param	string	$name		name属性の値
 * @param	string	$value		value属性の値
 * @param	string	$label		ラベル文字列
 * @param	string	$checked	選択されている場合はTRUE、そうでない場合はFALSE
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_checkbox($name, $value, $label, $checked, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['type'])) {
		$attributes['type'] = 'checkbox';
	}
	return tag_radio($name, $value, $label, $checked, $attributes);
}

/**
 * &lt;select&gt;タグを生成する
 *
 * 選択肢は、下記の要素から構成されたテーブル型配列を指定する。
 *
 * | label | 選択肢の表示名
 * | value | 選択肢の値
 * | group | 選択肢のグループ名
 *
 * @param	string	$name		name属性の値
 * @param	string	$options	選択肢
 * @param	string	$selected	選択中の値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_select($name, $options, $selected, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['name'])) {
		$attributes['name'] = $name;
	}
	if (isset($attributes['blank'])) {
		$blank = tag_open('option', array('value' => ''));
		$blank .= htmlspecialchars($attributes['blank']);
		$blank .= tag_close('option');
		unset($attributes['blank']);
	} else {
		$blank = '';
	}
	$selected = strval($selected);
	$str = tag_open('select', $attributes);
	$str .= $blank;
	$current_group = NULL;
	foreach ($options as $option) {
		if (isset($option['group']) && $current_group !== $option['group']) {
			if ($current_group !== NULL) {
				$str .= tag_close('optgroup');
			}
			$str .= tag_open('optgroup', array('label' => $option['group']));
			$current_group = $option['group'];
		}
		$options_attributes = array('value' => $option['value']);
		if (strval($option['value']) == $selected) {
			$options_attributes['selected'] = 'selected';
		}
		$str .= tag_open('option', $options_attributes);
		$str .= htmlspecialchars($option['label']);
		$str .= tag_close('option');
	}
	if ($current_group !== NULL) {
		$str .= tag_close('optgroup');
	}
	$str .= tag_close('select');
	return $str;
}

/**
 * &lt;textarea&gt;タグを生成する
 *
 * @param	string	$name		name属性の値
 * @param	string	$value		textareaに入力済みの値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_textarea($name, $value, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['name'])) {
		$attributes['name'] = $name;
	}
	$str = tag_open('textarea', $attributes);
	$str .= htmlspecialchars($value);
	$str .= tag_close('textarea');
	return $str;
}

/**
 * &lt;img&gt;タグを生成する
 *
 * @param	string	$src		src属性の値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_img($src, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['src'])) {
		$attributes['src'] = $src;
	}
	return tag_open('img', $attributes, TRUE);
}

/**
 * &lt;a href=""&gt;タグを生成する
 *
 * @param	string	$href		href属性の値
 * @param	string	$text		リンクに指定したいテキスト
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_a($href, $text = NULL, $attributes = NULL)
{
	$attributes = tag_parse_attributes($attributes);
	if (!isset($attributes['href'])) {
		$attributes['href'] = $href;
	}
	$str = tag_open('a', $attributes);
	$str .= $text;
	$str .= tag_close('a');
	return $str;
}

/**
 * 段落タグを生成する
 *
 * 改行コードをpタグに変換する
 *
 * @param	string	$str		テキスト
 * @return	string	生成したHTMLタグ
 * @package	html
 */
function tag_p($str)
{
	$str = trim(preg_replace("/\n\n+/", "\n", convert_newline($str)), "\n");
	$str = str_replace("\n", '</p><p>', htmlspecialchars($str));
	return '<p>' . $str . '</p>';
}

/**
 * 文字列または数値をエスケープする
 *
 * $valueが文字列の場合には、改行コードを&lt;br /&gt;に置換、
 * HTMLエスケープして出力する。
 *
 * $valueが数値の場合には、カンマ区切りで出力する。
 *
 * 文字幅が$widthより長い場合には、文字幅を制限して出力する。
 *
 * $valueが空文字列かNULLの場合には$defaultを出力する。
 *
 * @param	mixed	$value		出力する文字列または数値
 * @param	integer	$width		最大幅
 * @param	string	$default	デフォルトで出力する値
 * @package	html
 */
function h($value, $width = 0, $default = '&nbsp;')
{
	if (is_int($value)) {
		// 整数の出力
		echo number_format($value);
		return;
	}

	$value = strval($value);
	if ($value === '') {
		// 空文字列の出力
		echo $default;
		return;
	}

	// 文字幅が設定された場合
	if ($width != 0) {
		if (mb_strwidth($value, 'utf-8') > $width) {
			$value = mb_strimwidth($value, 0, $width, '..', 'utf-8');
		}
	}

	// 文字列のエスケープ結果
	return nl2br(htmlspecialchars($value));
}

/**
 * 文字列または数値を出力する
 *
 * @param	mixed	$value		出力する文字列または数値
 * @param	integer	$width		最大幅
 * @param	string	$default	デフォルトで出力する値
 * @see		h
 * @package	html
 */
function e($value, $width = 0, $default = '&nbsp;')
{
	echo h($value, $width, $default);
}

//------------------------------------------------------------------------------
// フォーム
//------------------------------------------------------------------------------

/**
 * フォームの入力値を設定する
 *
 * @param	mixed	$name	対象のフォームの名前、全て設定する場合にはNULL
 * @param	mixed	$value	フォームの入力値
 * @package	form
 */
function form_set_value($name, $value)
{
	global $__photon_form_value;
	if (!isset($__photon_form_value)) {
		$__photon_form_value = array();
	}
	array_set($__photon_form_value, $name, $value);
}

/**
 * フォームの入力値を取得する
 *
 * @param	mixed	$name	対象のフォームの名前、全て取得する場合にはNULL
 * @return	mixed	フォームの入力値
 * @package	form
 */
function form_get_value($name)
{
	global $__photon_form_value;
	if (!isset($__photon_form_value)) {
		return NULL;
	}
	return array_get($__photon_form_value, $name);
}

/**
 * フォームの入力値に指定した値が含まれているか調べる
 *
 * @param	mixed	$name	対象のフォームの名前、全て設定する場合にはNULL
 * @param	string	$check	含まれているか調べる値
 * @return	boolean	入力値に指定した値が含まれている場合にはTRUE、
 * 					含まれていない場合にはFALSE
 * @package	form
 */
function form_check_value($name, $check)
{
	$check = strval($check);
	$value = form_get_value($name);
	if (is_array($value)) {
		return in_array($check, $value);
	} else {
		return $check === strval($value);
	}
}

/**
 * フォームの選択肢を設定する
 *
 * @param	string	$name		選択肢の名前
 * @param	array	$options	選択肢の連想配列
 * @package	form
 */
function form_set_options($name, $options)
{
	global $__photon_form_options;
	$__photon_form_options[$name] = $options;
}

/**
 * フォームの選択肢を取得する
 *
 * form_set_optionsで選択肢が設定されている場合には、その値を返す。
 * また、(選択肢の名前)_get_optionsという名前の関数が存在する場合には、
 * その返り値を選択肢として返す。
 *
 * @param	string	$name		選択肢の名前
 * @return	array	選択肢の連想配列
 * @package	form
 */
function form_get_options($name)
{
	global $__photon_form_options;
	if (is_array($name)) {
		return $name;
	}
	if (isset($__photon_form_options[$name])) {
		return $__photon_form_options[$name];
	}
	$function = $name . '_get_options';
	if (function_exists($function)) {
		return call_user_func($function);
	}
	$function = 'get_' . $name . '_options';
	if (function_exists($function)) {
		return call_user_func($function);
	}
	return array();
}

/**
 * フォームを読込専用に設定する
 *
 * $nameをNULLに設定した場合、個別に設定しないフォームの状態を設定する。
 *
 * @param	mixed	$name		対象のフォームの名前、全て設定する場合にはNULL
 * @param	boolean	$is_static	読込専用ならTRUE、そうでない場合にはFALSE
 * @package	form
 */
function form_set_static($name = NULL, $is_static = TRUE)
{
	global $__photon_form_static;
	global $__photon_form_static_default;
	if ($name !== NULL) {
		$__photon_form_static[$name] = $is_static;
	} else {
		$__photon_form_static_default = $is_static;
	}
}

/**
 * フォームが読込専用か調べる
 *
 * @param	mixed	$name		対象のフォームの名前
 * @return	boolean	$is_static	読込専用ならTRUE、そうでない場合にはFALSE
 * @package	form
 */
function form_get_static($name)
{
	global $__photon_form_static;
	global $__photon_form_static_default;
	if (isset($__photon_form_static[$name])) {
		return $__photon_form_static[$name];
	} else {
		return $__photon_form_static_default;
	}
}

/**
 * フォームの開始タグを生成する
 *
 * @param	string	$action		action属性の値
 * @param	string	$method		method属性の値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_open($action, $method = 'post', $attributes = NULL)
{
	if (!isset($attributes['action'])) {
		$attributes['action'] = $action;
	}
	if (!isset($attributes['method'])) {
		$attributes['method'] = $method;
	}
	return tag_open('form', $attributes);
}

/**
 * アップロードを伴うフォームの開始タグを生成する
 *
 * @param	string	$action		action属性の値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_open_multipart($action, $attributes = NULL)
{
	if (!isset($attributes['enctype'])) {
		$attributes['enctype'] = 'multipart/form-data';
	}
	return form_open($action, 'post', $attributes);
}

/**
 * フォームの終了タグを生成する
 *
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_close()
{
	return tag_close('form');
}

/**
 * 読込専用のテキスト入力フォームを生成する
 *
 * 下記のように入力値と表示する値が同じ場合で使用する。
 * - form_text
 * - form_textarea
 * - form_radio_array
 * - form_checkbox_array
 * - form_select_array
 * - form_select_number
 *
 * 入力値が単一の値の場合には、HTMLエスケープ、改行コードを&lt;br /&gt;に
 * 変換して返す。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* static_glue	| 入力値が配列の場合に結合する文字列
 *
 * @param	mixed	$name	対象のフォームの名前
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_static($name)
{
	$value = form_get_value($name);
	if (is_array($value)) {
		$value = implode(config('static_glue'), $value);
	}
	return nl2br(htmlspecialchars($value));
}

/**
 * 読込専用の選択肢フォームを生成する
 *
 * 下記のように連想配列から選択する場合で使用する。
 * - form_radio_assoc
 * - form_checkbox_assoc
 * - form_select_assoc
 *
 * 入力値が単一の値の場合には、連想配列でルックアップして返す。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* static_glue	| 入力値が配列の場合に結合する文字列
 *
 * @param	mixed	$name		対象のフォームの名前
 * @param	mixed	$options	選択肢の連想配列、または選択肢の名前
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_static_assoc($name, $options)
{
	$value = form_get_value($name);
	$options = form_get_options($options);
	if (is_array($value)) {
		$array = array();
		foreach ($value as $v) {
			$array[] = $options[$v];
		}
		$value = implode(config('static_glue'), $array);
	} else {
		$value = $options[$value];
	}
	return nl2br(htmlspecialchars($value));
}

/**
 * 読込専用のパスワード入力フォームを生成する
 *
 * form_passwordのように入力値を出力できない場合で使用する。
 * 隠し文字を入力値の文字数だけ繰り返す。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* static_hidden	| パスワードの隠し文字
 *
 * @param	mixed	$name	対象のフォームの名前
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_static_password($name)
{
	$value = form_get_value($name);
	return str_repeat(config('static_hidden'), strlen($value));
}

/**
 * 読込専用の真偽値フォームを生成する
 *
 * 下記のように真偽値を選択する場合で使用する。
 * - form_radio
 * - form_checkbox
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* static_true	| 真の値の場合の文字列
 * |* static_false	| 偽の値の場合の文字列
 *
 * @param	mixed	$name	対象のフォームの名前
 * @param	mixed	$check	選択時の値
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_static_boolean($name, $check)
{
	if (form_check_value($name, $check)) {
		return config('static_true');
	} else {
		return config('static_false');
	}
}

/**
 * 単一行テキスト入力フォームを生成する
 *
 * @param	string	$name	対象のフォームの名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_text($name, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static($name);
	} else {
		return tag_input('text', $name, form_get_value($name), $attributes);
	}
}

/**
 * パスワード入力フォームを生成する
 *
 * @param	string	$name	対象のフォームの名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_password($name, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_password($name);
	} else {
		return tag_input('password', $name, form_get_value($name), $attributes);
	}
}

/**
 * ファイル入力フォームを生成する
 *
 * @param	string	$name	対象のフォームの名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_file($name, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static($name);
	} else {
		return form_hidden($name) . tag_input('file', $name, '', $attributes);
	}
}

/**
 * 隠しフォームを生成する
 *
 * @param	mixed	$names	対象のフォームの名前、またはフォームの名前の連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_hidden($names)
{
	if (func_num_args() != 1) {
		return form_hidden(func_get_args());
	} elseif (is_array($names)) {
		$str = '';
		foreach ($names as $name) {
			$str .= tag_hidden(array($name => form_get_value($name)));
		}
		return $str;
	} else if ($names === NULL) {
		return tag_hidden(form_get_value(NULL));
	} else {
		return form_hidden(array($names));
	}
}

/**
 * 複数行テキスト入力フォームを生成する
 *
 * @param	string	$name	対象のフォームの名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_textarea($name, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static($name);
	} else {
		return tag_textarea($name, form_get_value($name), $attributes);
	}
}

/**
 * ラジオボタンを生成する
 *
 * $labelにNULL以外の値を指定した場合、&lt;label&gt;タグによるラベルも生成する。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	string	$value		選択時の値
 * @param	string	$label		ラベルの文字列
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_radio($name, $value, $label = NULL, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_boolean($name, $value);
	} else {
		$checked = form_check_value($name, $value);
		return tag_radio($name, $value, $label, $checked, $attributes);
	}
}

/**
 * 連想配列から選択するラジオボタンを生成する
 *
 * $optionsは、選択時の値=>選択肢の文字列とした連想配列を指定する。
 * form_set_optionsで設定した選択肢の名前を指定することもできる。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$options	選択肢の連想配列、または選択肢の名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_radio_assoc($name, $options, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		$options = form_get_options($options);
		$html = '';
		foreach ($options as $value => $label) {
			$checked = form_check_value($name, $value);
			$html .= tag_radio($name, $value, $label, $checked, $attributes);
		}
		return $html;
	}
}

/**
 * 配列から選択するラジオボタンを生成する
 *
 * $optionsは、選択時の値・文字列の配列とする。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$array		選択肢の配列
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_radio_array($name, $array, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static($name);
	} else {
		$html = '';
		foreach ($array as $value) {
			$checked = form_check_value($name, $value);
			$html .= tag_radio($name, $value, $value, $checked, $attributes);
		}
		return $html;
	}
}

/**
 * チェックボックスを生成する
 *
 * $labelにNULL以外の値を指定した場合、&lt;label&gt;タグによるラベルも生成する。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	string	$value		選択時の値
 * @param	string	$label		ラベルの文字列
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_checkbox($name, $value, $label = NULL, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_boolean($name, $value);
	} else {
		$checked = form_check_value($name, $value);
		return tag_checkbox($name, $value, $label, $checked, $attributes);
	}
}

/**
 * 連想配列から選択するチェックボックスを生成する
 *
 * $optionsは、選択時の値=>選択肢の文字列とした連想配列を指定する。
 * form_set_optionsで設定した選択肢の名前を指定することもできる。
 *
 * $nameの終端が[]でない場合、[]を付け加える。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$options	選択肢の連想配列、または選択肢の名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_checkbox_assoc($name, $options, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		if (substr($name, -2) != '[]') {
			$name .= '[]';
		}
		$options = form_get_options($options);
		$html = '';
		foreach ($options as $value => $label) {
			$checked = form_check_value($name, $value);
			$html .= tag_checkbox($name, $value, $label, $checked, $attributes);
		}
		return $html;
	}
}

/**
 * 配列から選択するチェックボックスを生成する
 *
 * $optionsは、選択時の値・文字列の配列とする。
 *
 * $nameの終端が[]でない場合、[]を付け加える。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$array		選択肢の配列
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_checkbox_array($name, $array, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static($name);
	} else {
		if (substr($name, -2) != '[]') {
			$name .= '[]';
		}
		$html = '';
		foreach ($array as $value) {
			$checked = form_check_value($name, $value);
			$html .= tag_checkbox($name, $value, $value, $checked, $attributes);
		}
		return $html;
	}
}

/**
 * テーブル型配列から選択するドロップダウンを生成する
 *
 * 選択肢は、下記の要素から構成されたテーブル型配列を指定する。
 *
 * | label | 選択肢の表示名
 * | value | 選択肢の値
 * | group | 選択肢のグループ名
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$options	選択肢
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_select($name, $options, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		$selected = form_get_value($name);
		return tag_select($name, $options, $selected, $attributes);
	}
}

/**
 * 連想配列から選択するドロップダウンを生成する
 *
 * $optionsは、選択時の値=>選択肢の文字列とした連想配列を指定する。
 * form_set_optionsで設定した選択肢の名前を指定することもできる。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$options	選択肢の連想配列、または選択肢の名前
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_select_assoc($name, $options, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		$select_options = array();
		foreach (form_get_options($options) as $value => $label) {
			$select_options[] = array('value' => $value, 'label' => $label);
		}
		$selected = form_get_value($name);
		return tag_select($name, $select_options, $selected, $attributes);
	}
}

/**
 * 配列から選択するドロップダウンを生成する
 *
 * $optionsは、選択時の値・文字列の配列とする。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$options		選択肢の配列
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_select_array($name, $options, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		$select_options = array();
		foreach (form_get_options($options) as $value) {
			$select_options[] = array('value' => $value, 'label' => $value);
		}
		$selected = form_get_value($name);
		return tag_select($name, $options, $selected, $attributes);
	}
}

/**
 * 数値から選択するドロップダウンを生成する
 *
 * @param	string	$name		対象のフォームの名前
 * @param	array	$min		選択肢の下限値
 * @param	array	$max		選択肢の上限値
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @package	form
 */
function form_select_number($name, $min, $max, $attributes = NULL)
{
	if (form_get_static($name)) {
		return form_static_assoc($name, $options);
	} else {
		$options = array();
		for ($value = $min; $value <= $max; $value++) {
			$options[] = array('value' => $value, 'label' => $value);
		}
		$selected = form_get_value($name);
		return tag_select($name, $options, $selected, $attributes);
	}
}

/**
 * ファイルのアップロードフォームを生成する
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* form_upload_remove	| 削除のチェックボックスのラベル
 * |* form_upload_url		| アップロード済みの基準URL
 * |* form_upload_link		| アップロード済みのテキスト
 *
 * @param	string	$name		対象のフォームの名前
 * @param	boolean	$remove		削除できる場合にはTRUE、そうでない場合にはFALSE
 * @param	boolean	$link		アップロード済みのファイルへのリンクを
 * 								設置する場合にはTRUE、そうでない場合にはFALSE
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @see		form_upload_file
 * @package	form
 */
function form_upload($name, $remove = TRUE, $link = TRUE, $attributes = NULL)
{
	$html = form_file($name, $attributes);
	$value = form_get_value($name);
	if ($value === '') {
		return $html;
	}
	if ($remove) {
		$remove_name = '__remove_' . $name;
		$html .= form_checkbox($remove_name, 'y', config('form_upload_remove'));
	}
	if ($link) {
		$url = config('form_upload_url') . form_get_value($name);
		$ext = strtolower(extension($value));
		if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
			$text = tag_img($url);
		} else {
			$text = config('form_upload_link');
		}
		$html .= tag_a($url, $text);
	}
	return $html;
}

/**
 * 日時の選択肢フォームを生成する
 *
 * 日時選択のフォーマットには、下記の書式文字列を使用できる。
 *
 * |* {y}	|近年の選択肢 (2010年〜2100年)
 * |* {p}	|過去の年の選択肢 (1900年〜今年)
 * |* {m}	|月の選択肢
 * |* {d}	|日の選択肢
 * |* {h}	|時の選択肢
 * |* {i}	|分の選択肢
 * |* {s}	|秒の選択肢
 *
 * デフォルトでは{y}/{m}/{d}が使用される。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* form_date_year_min	| {y}の選択肢の最小値
 * |* form_date_year_max	| {y}の選択肢の最大値
 * |* form_date_past_min	| {p}の選択肢の最小値
 *
 * 選択された値は、y, m, d, h, i, sをキーとする連想配列となるので、
 * MySQLデータベースに格納する際には、form_date_convertで変換する。
 *
 * フォームの入力値は、MySQLデータベースの形式YYYY-MM-DD HH:II:SSか、
 * y, m, d, h, i, sをキーとする連想配列に対応する。
 *
 * @param	string	$name		対象のフォームの名前
 * @param	boolean	$format		日時選択のフォーマット
 * @param	mixed	$attributes	追加するタグの属性の文字列、または連想配列
 * @return	string	生成したHTMLタグ
 * @see		form_date_convert
 * @package	form
 */
function form_date($name, $format = NULL, $attributes = NULL)
{
	$value = form_get_value($name);

	// MySQLデータベースの形式から配列形式に変換
	if (is_string($value)) {
		$array = sscanf($value, '%04d-%02d-%02d %02d:%02d:%02d');
		form_set_value($name . '[y]', intval($array[0]));
		form_set_value($name . '[m]', intval($array[1]));
		form_set_value($name . '[d]', intval($array[2]));
		form_set_value($name . '[h]', intval($array[3]));
		form_set_value($name . '[i]', intval($array[4]));
		form_set_value($name . '[s]', intval($array[5]));
	}

	// 日時のドロップダウンを生成
	$vars = array();
	$y_min = config('form_date_year_min');
	$y_max = config('form_date_year_max');
	$p_min = config('form_date_past_min');
	$p_max = intval(date('Y'));
	$vars['y'] = form_select_number($name . '[y]', $y_min, $y_max, $attributes);
	$vars['p'] = form_select_number($name . '[y]', $p_min, $p_max, $attributes);
	$vars['m'] = form_select_number($name . '[m]', 1, 12, $attributes);
	$vars['d'] = form_select_number($name . '[d]', 1, 31, $attributes);
	$vars['h'] = form_select_number($name . '[h]', 0, 23, $attributes);
	$vars['i'] = form_select_number($name . '[i]', 0, 59, $attributes);
	$vars['s'] = form_select_number($name . '[s]', 0, 59, $attributes);

	// フォーマットに合わせて選択肢のHTMLを生成
	if ($format === NULL) {
		$format = config('form_date_format');
	}
	return embed($format, $vars);
}

/**
 * アップロードされたファイルの情報を取得する
 *
 * フォームの名前がa[b][c]のように配列の形式にも対応している。
 *
 * @param	string	$name	対象のフォームの名前
 * @param	string	$key	$_FILESのキーの名前
 * @return	string	アップロードされたファイルの情報
 * @see		form_upload
 * @package	form
 */
function form_get_file($name, $key)
{
	// 名前の先頭と先頭以降に分ける
	$keys = explode('.', str_replace(array('][', '[', ']'), '.', $name));
	$head = array_shift($keys);

	// $_FILESの値を取得する
	return array_get($_FILES[$head][$key], $keys);
}

/**
 * アップロードされたファイルを保存する
 *
 * アップロードされたファイルは、一意の名前を付け、$dirに保存する。
 * $dataの該当値には、$dirと保存した名前を結合した文字列を設定する。
 *
 * form_uploadで削除フラグを選択した場合には、
 * $dataの該当値を空文字列に設定する。
 *
 * アップロードした拡張子が$extと異なる場合には、エラーとなる。
 * $extにNULLを設定した場合には、全ての拡張子のファイルが受け入れられる。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* form_upload_dir	| アップロード先のディレクトリ
 * |* error_upload		| アップロードに失敗した場合のエラーメッセージ
 *
 * @param	array	$data	入力データの連想配列
 * @param	string	$name	対象のフォームの名前
 * @param	string	$dir	アップロード先のディレクトリ
 * @param	mixed	$extensions	許可される拡張子の連想配列、カンマ区切りの文字列
 * @see		form_upload
 * @package	form
 */
function form_upload_file(&$data, $name, $dir = NULL, $extensions = NULL)
{
	$error = form_get_file($name, 'error');
	if ($error === NULL) {
		return;
	}
	if ($error == UPLOAD_ERR_OK) {
		// 元のファイルの拡張子を取得し、チェックする
		$ext = extension(form_get_file($name, 'name'));
		if ($extensions !== NULL) {
			if (!is_array($extensions)) {
				$extensions = explode(',', $extensions);
			}
			if (!in_array($ext, $extensions)) {
				return form_set_error($name, config('error_upload'));
			}
		}

		// ファイルの置き場所
		$tmp_name = form_get_file($name, 'tmp_name');

		// アップロード先のディレクトリを求める
		$dir = rtrim($dir, '/');
		$base_dir = rtrim(config('form_upload_dir'), '/');
		$dest_dir = $base_dir . '/' . $dir . '/';

		// アップロード先のディレクトリを生成する
		if (!file_exists($dest_dir)) {
			mkdir($dest_dir, 0777, TRUE);
		}

		// アップロード先のファイル名
		$filename = md5_file($tmp_name) . '.' . $ext;
		$dest_path = $dest_dir . $filename;
		$value = $dir . '/' . $filename;

		if (file_exists($dest_path)) {
			// 同一のファイルが存在している場合
			unlink($tmp_name);
			return array_set($data, $name, $value);
		} else if (move_uploaded_file($tmp_name, $dest_path)) {
			// ファイルのアップロードに成功
			return array_set($data, $name, $value);
		} else {
			// アップロードに失敗
			return form_set_error($name, config('error_upload'));
		}
	} elseif ($error != UPLOAD_ERR_NO_FILE) {
		// エラー
		return form_set_error($name, config('error_upload'));
	}

	// ファイル削除
	$remove_name = '__remove_' . $name;
	if (array_get($_REQUEST, $remove_name) == 'y') {
		array_set($data, $name, '');
	}
}

/**
 * form_dateで入力された日時をMySQLデータベース形式に変換する
 *
 * $dataの該当値がy, m, d, h, i, sをキーとした連想配列である場合、
 * yyyy-mm-dd hh:ii:ss形式による文字列を設定する。
 *
 * @param	array	$data	入力データの連想配列
 * @param	string	$name	対象のフォームの名前
 * @see		form_date
 * @package	form
 */
function form_date_convert(&$data, $name)
{
	$value = array_get($data, $name);
	if (is_array($value)) {
		$args = array();
		$y = intval($value['y']);
		$m = intval($value['m']);
		$d = intval($value['d']);
		$str = sprintf('%04d-%02d-%02d', $y, $m, $d);
		if (isset($value['h'])) {
			$h = intval($value['h']);
			$i = intval($value['i']);
			$s = intval($value['s']);
			$str .= sprintf(' %02d:%02d:%02d', $h, $i, $s);
		}
		array_set($data, $name, $str);
	}
}

//------------------------------------------------------------------------------
// フィルタとバリデーション
//------------------------------------------------------------------------------

/**
 * フォームのエラーを設定する
 *
 * @param	string	$name		対象のフォームの名前
 * @param	string	$message	エラーメッセージの文字列
 * @param	array	$vars		エラーメッセージの変数に埋め込む連想配列
 * @package	validate
 */
function form_set_error($name, $message, $vars = array())
{
	global $__photon_form_error;
	$__photon_form_error[$name] = embed($message, $vars);
}

/**
 * フォームのエラーを取得する
 *
 * @param	string	$name		対象のフォームの名前
 * @return	string	エラーメッセージの文字列、エラーが発生していない場合にはNULL
 * @package	validate
 */
function form_get_error($name)
{
	global $__photon_form_error;
	if ($name === NULL) {
		return $__photon_form_error;
	} else if (isset($__photon_form_error[$name])) {
		return $__photon_form_error[$name];
	} else {
		return NULL;
	}
}

/**
 * フォームにエラーが発生しているかを調べる
 *
 * @return	boolean	エラーが発生している場合にはTRUE、そうでない場合にはFALSE
 * @package	validate
 */
function form_has_error()
{
	global $__photon_form_error;
	return count($__photon_form_error) > 0 ? TRUE : FALSE;
}

/**
 * フォームのエラーをHTMLで取得する
 *
 * 複数引数が指定された場合、エラーが発生している、
 * 先頭の引数のフォームのエラーメッセージを取得する。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* error_tag_open	| エラーメッセージの開始タグ
 * |* error_tag_close	| エラーメッセージの終了タグ
 *
 * @param	string	$name	対象のフォームの名前
 * @return	string	エラーメッセージのHTML、エラーが発生していない場合にはNULL
 * @package	validate
 */
function form_error($name)
{
	foreach (func_get_args() as $name) {
		$message = form_get_error($name);
		if ($message !== NULL) {
			$html = config('error_tag_open');
			$html .= htmlspecialchars($message);
			$html .= config('error_tag_close');
			return $html;
		}
	}
	return NULL;
}

/**
 * フィルタ・バリデーションルールを初期化する
 *
 * @package	validate
 */
function rule_clean()
{
	global $__photon_form_rule;
	$__photon_form_rule = array();
}

/**
 * フィルタ・バリデーションルールを設定する
 *
 * @param	string	$name	対象のフォームの名前
 * @param	array	$rule	ルールの連想配列
 * @package	validate
 */
function rule($name, $rule = array())
{
	global $__photon_form_rule;
	$__photon_form_rule[$name] = $rule;
}

/**
 * 入力データのフィルタ処理を行う
 *
 * rule関数で下記の項目について設定ができる。
 *
 * |* kana |mb_convert_kanaの引数、変換しない場合はNULL。
 * |* trim |トリミングするならy。デフォルトはy
 * |* case |大文字にするならupper、小文字にするならlower、変換しない場合はNULL
 * |* default |入力データが空の場合のデフォルト値
 *
 * @param	array	$data	フィルタ処理を行う連想配列
 * @return	array	フィルタ処理の結果の連想配列
 * @package	validate
 */
function filter($data)
{
	global $__photon_form_rule;
	$return = array();
	foreach ($__photon_form_rule as $name => $rule) {
		$value = array_get($data, $name);
		$value = __filter($value, $rule);
		array_set($return, $name, $value);
	}
	return $return;
}

/** @ignore */
function __filter($value, $rule)
{
	// 配列の場合の処理
	if (is_array($value)) {
		foreach ($value as $k => $v) {
			$value[$k] = __filter($v, $rule);
		}
		return $value;
	}

	// 入力データが空の場合の処理
	if (isset($rule['default'])) {
		if ($value === '' || $value === NULL) {
			$value = $rule['default'];
		}
	}

	// 多バイト文字の変換
	$kana = isset($rule['kana']) ? $rule['kana'] : 'saKV';
	if ($kana !== '') {
		$value = mb_convert_kana($value, $kana, 'utf-8');
	}

	// 両端の空白を除去する
	$trim = isset($rule['trim']) ? $rule['trim'] : 'y';
	if (is_true($trim)) {
		$value = trim($value);
	}

	// 大文字・小文字の変換
	$case = isset($rule['case']) ? $rule['case'] : '';
	if ($case == 'upper') {
		$value = strtoupper($value);
	} elseif ($case == 'lower') {
		$value = strtolower($value);
	}

	// 改行コードを変換する
	$value = convert_newline($value);

	return $value;
}

/**
 * 入力データのバリデーション処理を行う
 *
 * rule関数で下記の項目について設定ができる。
 *
 * |* required	| 入力必須項目ならy
 * |* min_bytes	| 最小バイト数
 * |* max_bytes	| 最大バイト数
 * |* min_chars	| 最小文字数
 * |* max_chars	| 最大文字数
 * |* min_width	| 最小文字幅
 * |* max_width	| 最大文字幅
 * |* min_lines	| 最小行数
 * |* max_lines	| 最大行数
 * |* min_value	| 最小入力値
 * |* max_value	| 最大入力値
 * |* matches	| 一致すべき他の入力フォームの名前
 * |* options	| 選択肢の連想配列、名前の文字列
 * |* preg		| Perl正規表現形式による書式
 * |* type		| alpha		| 英字
 * |^			| digit		| 数字
 * |^			| alnum		| 英数字
 * |^			| alnum_dash| 英数字、アンダーバー("_")、ダッシュ("-")
 * |^			| graph		| 空白を除く表示可能な文字列
 * |^			| integer	| 整数 (マイナスを含む)
 * |^			| natural	| 自然数 (0以上の整数)
 * |^			| decimal	| 小数値を含む数値
 * |^			| mail		| メールアドレス
 * |^			| ipv4		| IPv4アドレス
 * |^			| hiragana	| ひらがな
 * |^			| katakana	| カタカナ
 * |^			| url		| URL
 *
 * @param	array	$data	バリデーションを行う連想配列
 * @return	boolean	エラーが発生しなかった場合にはTRUE、そうでない場合にはFALSE
 * @package	validate
 */
function validate($data)
{
	global $__photon_form_rule;
	$return = array();
	foreach ($__photon_form_rule as $name => $rule) {
		$value = array_get($data, $name);
		__validate($data, $name, $rule, $value);
	}
	return form_has_error() ? FALSE : TRUE;
}

/** @ignore */
function __validate($data, $name, $rule, $value)
{
	// 入力の一致を調べる
	if (isset($rule['matches'])) {
		if ($value !== strval(array_get($data, $rule['matches']))) {
			return form_set_error($name, config('error_matches'), $rule);
		}
	}

	// 入力必須
	$required = isset($rule['required']) ? is_true($rule['required']) : FALSE;
	if (is_array($value)) {
		if (count($value) == 0) {
			if ($required) {
				form_set_error($name, config('error_required'), $rule);
			}
		} else {
			foreach ($value as $v) {
				$v = strval($v);
				if ($v !== '') {
					__validate($data, $name, $rule, $v);
				}
			}
		}
		return;
	} else {
		$value = strval($value);
		if ($value === '') {
			if ($required) {
				form_set_error($name, config('error_required'), $rule);
			}
			return;
		}
	}

	// バイト数の範囲
	$bytes = strlen($value);
	if (isset($rule['min_bytes']) && $bytes < $rule['min_bytes']) {
		return form_set_error($name, config('error_min_bytes'), $rule);
	}
	if (isset($rule['max_bytes']) && $bytes > $rule['max_bytes']) {
		return form_set_error($name, config('error_max_bytes'), $rule);
	}

	// 文字数の範囲
	$chars = mb_strlen($value, 'utf-8');
	if (isset($rule['min_chars']) && $chars < $rule['min_chars']) {
		return form_set_error($name, config('error_min_chars'), $rule);
	}
	if (isset($rule['max_chars']) && $chars > $rule['max_chars']) {
		return form_set_error($name, config('error_max_chars'), $rule);
	}

	// 文字幅の範囲
	$width = mb_strwidth($value, 'utf-8');
	if (isset($rule['min_width']) && $width < $rule['min_width']) {
		return form_set_error($name, config('error_min_width'), $rule);
	}
	if (isset($rule['max_width']) && $width > $rule['max_width']) {
		return form_set_error($name, config('error_max_width'), $rule);
	}

	// 行数の範囲
	$lines = substr_count($value, "\n");
	if (isset($rule['min_lines']) && $lines < $rule['min_lines']) {
		return form_set_error($name, config('error_min_lines'), $rule);
	}
	if (isset($rule['max_lines']) && $lines > $rule['max_lines']) {
		return form_set_error($name, config('error_max_lines'), $rule);
	}

	// 値の範囲
	if (isset($rule['min_value']) && $value < $rule['min_value']) {
		return form_set_error($name, config('error_min_value'), $rule);
	}
	if (isset($rule['max_value']) && $value > $rule['max_value']) {
		return form_set_error($name, config('error_max_value'), $rule);
	}

	// 選択肢からの選択可を調べる
	if (isset($rule['options'])) {
		$options = form_get_options($rule['options']);
		if (!isset($options[$value])) {
			return form_set_error($name, config('error_options'), $rule);
		}
	}

	// Perl正規表現形式による書式
	if (isset($rule['preg'])) {
		if (!preg_match($rule['preg'], $value)) {
			return form_set_error($name, config('error_preg'), $rule);
		}
	}

	// 入力の書式
	$type = isset($rule['type']) ? $rule['type'] : '';
	$value_int = intval($value);
	switch ($type) {
		case 'alpha':
			if (preg_match('/[^a-zA-Z]/', $value)) {
				return form_set_error($name, config('error_alpha'), $rule);
			}
			break;
		case 'digit':
			if (preg_match('/[^0-9]/', $value)) {
				return form_set_error($name, config('error_digit'), $rule);
			}
			break;
		case 'alnum':
			if (preg_match('/[^a-zA-Z0-9]/', $value)) {
				return form_set_error($name, config('error_alnum'), $rule);
			}
			break;
		case 'alnum_dash':
			if (preg_match('/[^a-zA-Z0-9_-]/', $value)) {
				return form_set_error($name, config('error_alnum_dash'), $rule);
			}
			break;
		case 'graph':
			if (preg_match('/[^[:graph:]]/', $value)) {
				return form_set_error($name, config('error_graph'), $rule);
			}
			break;
		case 'integer':
			if (strval($value_int) !== $value) {
				return form_set_error($name, config('error_integer'), $rule);
			}
			break;
		case 'natural':
			if (strval($value_int) !== $value || $value_int < 0) {
				return form_set_error($name, config('error_natural'), $rule);
			}
			break;
		case 'decimal':
			if (!is_numeric($value)) {
				return form_set_error($name, config('error_decimal'), $rule);
			}
			break;
		case 'mail':
			if (!is_mail($value)) {
				return form_set_error($name, config('error_mail'), $rule);
			}
			break;
		case 'ipv4':
			if (!is_ipv4($value)) {
				return form_set_error($name, config('error_ipv4'), $rule);
			}
			break;
		case 'hiragana':
			if (!is_hiragana($value)) {
				return form_set_error($name, config('error_hiragana'), $rule);
			}
			break;
		case 'katakana':
			if (!is_katakana($value)) {
				return form_set_error($name, config('error_katakana'), $rule);
			}
			break;
		case 'url':
			if (parse_url($value)== -1) {
				return form_set_error($name, config('error_url'), $rule);
			}
			break;
		default:
			break;
	}
}

//------------------------------------------------------------------------------
// データベース
//------------------------------------------------------------------------------

/**
 * MySQLデータベースに接続する
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* db_hostname	| データベースのホスト名
 * |* db_username	| データベースのユーザ名
 * |* db_password	| データベースのパスワード
 * |* db_database	| データベースのデータベース
 * |* db_charset	| データベースの文字コード
 *
 * 失敗した場合にはfatal関数で強制終了する。
 *
 * @return	resource	データベース接続ID
 * @package	db
 */
function db_connect()
{
	global $__photon_link;

	// 既に接続済みなら、接続IDを返す
	if (isset($__photon_link) && $__photon_link !== FALSE) {
		return $__photon_link;
	}

	// MySQLデータベースに接続する
	$db_hostname = config('db_hostname');
	$db_username = config('db_username');
	$db_password = config('db_password');
	$__photon_link = mysqli_connect($db_hostname, $db_username, $db_password);
	if ($__photon_link === FALSE) {
		fatal(config('error_db_connect'));
	}

	// MySQLデータベースを選択する
	$db_database = config('db_database');
	if (mysqli_select_db($__photon_link, $db_database) === FALSE) {
		fatal(config('error_db_select'));
	}

	// 文字コードをutf-8に設定する
	$db_charset = config('db_charset');
	if (mysqli_set_charset($__photon_link, $db_charset) === FALSE) {
		fatal(config('error_db_charset'));
	} else {
		if (mysqli_query($__photon_link, "SET NAMES " . $db_charset) === FALSE) {
			fatal(config('error_db_charset'));
		}
	}

	// 接続IDを返す
	return $__photon_link;
}

/**
 * MySQLデータベースの接続を閉じる
 *
 * @package	db
 */
function db_close()
{
	global $__photon_link;
	if (isset($__photon_link) && $__photon_link !== FALSE) {
		mysqli_close($__photon_link);
		$__photon_link = FALSE;
	}
}

/**
 * SQLクエリを実行する
 *
 * 失敗した場合にはfatal関数で強制終了する
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* log_query	| クエリ履歴のテーブル名。空文字列で無効化
 *
 * @param	string	$query	SQLクエリを表す文字列
 * @return	mixed	結果のリソースID
 * @package	db
 */
function db_query($query)
{
	global $__photon_log_query;

	$link = db_connect();

	// クエリを実行する
	$result = mysqli_query($link, $query);

	// クエリに失敗した場合の処理
	if ($result === FALSE) {
		$message = config('error_db_query') . "\n";
		$message .= mysqli_error($link);
		$message .= "\n" . $query;
		fatal($message);
	}

	// クエリ履歴に記録する
	$log_query = config('log_query');
	if ($log_query !== '') {
		if (!isset($__photon_log_query)) {
			$__photon_log_query = TRUE;
			db_insert($log_query, compact('query'));
			unset($__photon_log_query);
		}
	}

	return $result;
}

/**
 * 文字列を安全にSQLクエリ文に埋め込める形式にエスケープする
 *
 * mysqli_real_escape_string関数を呼び出すため、
 * データベースへの接続を必要とする。
 *
 * @param	string	$string	対象の文字列
 * @return	string	エスケープされた文字列
 * @package	db
 */
function db_escape($string)
{
	return mysqli_escape_string(db_connect(), $string);
}

/**
 * 文字列をクォートする
 *
 * table.fieldの形式の場合、`table`.`field`の形式になる。
 * fieldのみの場合、`field`の形式になる。
 * いずれでもない場合、そのまま文字列を返す。
 *
 * mysqli_real_escape_string関数を呼び出すため、
 * データベースへの接続を必要とする。
 *
 * @param	string	$field	対象の文字列
 * @return	string	エスケープされた文字列
 * @package	db
 */
function db_quote_field($field)
{
	if (preg_match('/^([a-z0-9_]+)(?:.([a-z0-9_*]+))?$/i', $field, $array)) {
		$str = '`' . db_escape($array[1]) . '`';
		if (isset($array[2])) {
			if ($array[2] == '*') {
				$str .= '.*';
			} else {
				$str .= '.`' . db_escape($array[2]) . '`';
			}
		}
		return $str;
	} else {
		return $field;
	}
}

/**
 * テーブルのカラムについての情報を取得する
 *
 * DESCRIBE構文を実行する。結果はグローバル変数でキャッシュする。
 *
 * @param	string	$table	テーブル名
 * @return	array	テーブルのカラムについての情報
 * @package	db
 */
function db_describe($table)
{
	global $__photon_describe;
	if (isset($__photon_describe[$table])) {
		return $__photon_describe[$table];
	} else {
		$describe = db_select_table('describe `' . $table . '`', 'Field');
		return $__photon_describe[$table] = $describe;
	}
}

/**
 * テーブルのプライマリキーを取得する
 *
 * @param	string	$table	テーブル名
 * @return	string	プライマリキーのフィールド名
 * @package	db
 */
function db_primary_key($table)
{
	global $__photon_pk;
	if (isset($__photon_pk[$table])) {
		return $__photon_pk[$table];
	} else {
		$describe = db_describe($table);
		foreach ($describe as $column) {
			if ($column['Key'] == 'PRI') {
				return $__photon_pk[$table] = $column['Field'];
			}
		}
		return NULL;
	}
}

/**
 * テーブルのデータを挿入可能な形式に変換する
 *
 * $dataの要素名が$tableのフィールド名と一致するものだけを抽出する。
 *
 * @param	string	$table	テーブル名
 * @param	array	$data	対象の連想配列
 * @return	string	プライマリキーのフィールド名
 * @package	db
 */
function db_convert($table, $data)
{
	$keys = array_keys(db_describe($table));
	$return = array();
	foreach ($keys as $key) {
		if (isset($data[$key])) {
			$return[$key] = $data[$key];
		}
	}
	return $return;
}

/**
 * トランザクションを開始する
 *
 * @package	db
 */
function db_start_transaction()
{
	global $__photon_transaction;
	$__photon_transaction = true;
	db_query('START TRANSACTION');
}

/**
 * コミット処理を行う
 *
 * @package	db
 */
function db_commit()
{
	global $__photon_transaction;
	if ($__photon_transaction) {
		$__photon_transaction = false;
		db_query('COMMIT');
	}
}

/**
 * ロールバック処理を行う
 *
 * @package	db
 */
function db_rollback()
{
	global $__photon_link;
	global $__photon_transaction;
	if ($__photon_link && $__photon_transaction) {
		$__photon_transaction = false;
		db_query('ROLLBACK');
	}
}

/**
 * テーブルを削除する
 *
 * @param	string|array	$table	テーブル名
 *
 * @package	db
 */
function db_table_drop($table)
{
	if (is_array($table)) {
		foreach ($table as $tbl) {
			db_table_drop($tbl);
		}
	} elseif (is_string($table)) {
		db_query('DROP TABLE IF EXISTS ' . db_quote_field($table));
	}
}

/**
 * テーブル名を変更する
 *
 * @param	string	$old_table	現在のテーブル名
 * @param	string	$new_table	新しいテーブル名
 *
 * @package	db
 */
function db_table_rename($old_table, $new_table)
{
	db_query('ALTER TABLE ' . db_quote_field($old_table) . ' RENAME TO ' . db_quote_field($new_table));
}

/**
 * テーブルを複製する
 *
 * @param	string	$old_table	現在のテーブル名
 * @param	string	$new_table	新しいテーブル名
 * @param	boolean	$with_data	データのコピーをするか？
 *
 * @package	db
 */
function db_table_copy($old_table, $new_table, $with_data)
{
	db_query('CREATE TABLE ' . db_quote_field($new_table) . ' LIKE ' . db_quote_field($old_table));
	if ($with_data) {
		db_query('INSERT INTO ' . db_quote_field($new_table) . ' SELECT * FROM ' . db_quote_field($old_table));
	}
}

/**
 * クエリ文を実行し、結果をレコードの配列で取得する
 *
 * @param	string	$query		クエリ文
 * @param	string	$key_field	レコードのキーとするフィールド名
 * @return	array	結果のレコードの配列 (結果が空の場合、空配列)
 * @package	db
 */
function db_select_table($query, $key_field = NULL)
{
	$result = db_query($query);
	$return = array();
	while ($row = mysqli_fetch_assoc($result)) {
		if ($key_field === NULL) {
			$return[] = $row;
		} else {
			$return[$row[$key_field]] = $row;
		}
	}
	mysqli_free_result($result);
	return $return;
}

/**
 * クエリ文を実行し、結果をレコードで取得する
 *
 * @param	string	$query		クエリ文
 * @param	string	$row_number	取得する行番号
 * @return	array	結果のレコード (結果が空の場合、FALSE)
 * @package	db
 */
function db_select_row($query, $row_number = 0)
{
	$result = db_query($query);
	if ($row_number != 0) {
		mysqli_data_seek($result, $row_number);
	}
	$return = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $return;
}

/**
 * クエリ文を実行し、結果を列の連想配列で取得する
 *
 * @param	string	$query			クエリ文
 * @param	string	$value_field	値として取得するフィールド名
 * @param	string	$key_field		キーとして取得するフィールド名
 * @return	array	結果の連想配列 (結果が空の場合、空配列)
 * @package	db
 */
function db_select_column($query, $value_field = NULL, $key_field = NULL)
{
	$result = db_query($query);
	$return = array();
	while ($row = mysqli_fetch_assoc($result)) {
		if ($value_field === NULL) {
			$value = current($row);
		} else {
			$value = $row[$value_field];
		}
		if ($key_field === NULL) {
			$return[] = $value;
		} else {
			$return[$row[$key_field]] = $value;
		}
	}
	mysqli_free_result($result);
	return $return;
}

/**
 * クエリ文を実行し、結果の値を取得する
 *
 * @param	string	$query			クエリ文
 * @param	string	$value_field	値として取得するフィールド名
 * @param	string	$row_number		取得する行番号
 * @return	string	取得結果の値 (結果が空の場合、FALSE)
 * @package	db
 */
function db_select_value($query, $value_field = NULL, $row_number = 0)
{
	$result = db_query($query);
	if ($row_number != 0) {
		mysqli_data_seek($result, $row_number);
	}
	$row = mysqli_fetch_assoc($result);
	if ($row === FALSE) {
		$return = FALSE;
	} else if ($value_field === NULL) {
		$return = current($row);
	} else {
		$return = $row[$value_field];
	}
	mysqli_free_result($result);
	return $return;
}

/**
 * 条件をフィールド名と値で指定し、レコードを取得する
 *
 * $cond_fieldが省略された場合、テーブルのプライマリキーを使用する。
 *
 * @param	string	$table		テーブル名
 * @param	string	$cond_value	条件の値
 * @param	string	$cond_field	条件のフィールド名
 * @return	array	結果のレコード
 * @package	db
 */
function db_select_at($table, $cond_value, $cond_field = NULL)
{
	if ($cond_field === NULL) {
		$cond_field = db_primary_key($table);
		if ($cond_field === NULL) {
			fatal(config('error_primary_key'));
		}
	}
	$query = 'SELECT * FROM `' . db_escape($table);
	$query .= '` WHERE `' . db_escape($cond_field) . "`='";
	$query .= db_escape($cond_value) . "' LIMIT 1";
	return db_select_row($query);
}

/**
 * テーブルの行を取得する
 *
 * @param	string	$table	テーブル名
 * @param	string	$id		プライマリキーの値
 * @return	array	結果のレコード
 * @package	db
 */
function db_get_at($table, $id)
{
	global $__db_cache;
	if (!isset($__db_cache[$table][$id])) {
		$__db_cache[$table][$id] = db_select_at($table, $id);
	}
	return $__db_cache[$table][$id];
}

/**
 * 条件を連想配列で指定し、レコードの配列を取得する
 *
 * @param	string	$table		テーブル名
 * @param	string	$cond		条件の連想配列
 * @return	array	結果のレコード
 * @package	db
 */
function db_select($table, $cond = array())
{
	$query = 'SELECT * FROM `' . db_escape($table);
	$query .= '` WHERE ' . __db_where_assoc($cond);
	return db_select_table($query);
}

/**
 * テーブルから選択肢を生成する
 *
 * フォームの選択肢として有用な選択肢の連想配列を生成する。
 *
 * $nameはデフォルトでname、title、プライマリキーを使用する。
 * $valueはデフォルトでプライマリキーを使用する。
 *
 * 選択肢の連想配列は、orderという名前のフィールドが存在する場合には、
 * orderの数値の降順となるが、存在しない場合には$valueの順序となる。
 *
 * @param	string	$table	テーブル名
 * @param	string	$name	選択肢のラベルのフィールド名
 * @param	string	$value	選択肢の値のフィールド名
 * @param	array	$cond	選択肢の条件
 * @return	array	選択肢の連想配列
 * @package	db
 */
function db_select_options($table, $name = NULL, $value = NULL, $cond = NULL)
{
	$describe = db_describe($table);

	// $nameが省略された場合
	if ($name === NULL) {
		if (isset($describe['name'])) {
			$name = 'name';
		} elseif (isset($describe['title'])) {
			$name = 'title';
		} else {
			$name = db_primary_key($table);
		}
	}

	// $valueが省略された場合
	if ($value === NULL) {
		$value = db_primary_key($table);
	}

	// クエリ文を構築
	$query = 'SELECT `' . db_escape($name) . '`,`';
	$query .= db_escape($value) . '`FROM `' . db_escape($table);
	$query .= '` WHERE ' . __db_where_assoc(db_convert($table, $cond));
	if (isset($describe['order'])) {
		$query .= ' ORDER BY `order` DESC';
	} else {
		$query .= ' ORDER BY `' . db_escape($value) . '`';
	}

	// クエリを実行
	return db_select_column($query, $name, $value);
}

/**
 * テーブルにレコードを挿入する
 *
 * createdとupdatedが現在の日時に設定される。
 * $idでプライマリキーの値を指定できる。
 *
 * @param	string	$table		テーブル名
 * @param	string	$data		挿入するレコードの連想配列
 * @param	string	$id			プライマリキーの値
 * @return	integer	プライマリキーの値
 * @package	db
 */
function db_insert($table, $data, $id = NULL)
{
	// データの準備
	$pk = db_primary_key($table);
	$data['created'] = $data['updated'] = __db_timestamp();
	$data = db_convert($table, $data);
	if ($pk !== NULL) {
		if ($id === NULL) {
			unset($data[$pk]);
		} else {
			$data[$pk] = $id;
		}
	}

	// INSERT文を生成
	$query = __db_insert($table, $data);

	// クエリを実行
	db_query($query);

	// 生成したIDを返す
	return mysqli_insert_id(db_connect());
}

/**
 * テーブルを更新する
 *
 * updatedが現在の日時に設定される。
 *
 * @param	string	$table	テーブル名
 * @param	string	$cond	更新する条件の連想配列
 * @param	string	$data	更新する値の連想配列
 * @return	integer	更新されたレコード数
 * @package	db
 */
function db_update($table, $data, $cond)
{
	// データの準備
	$pk = db_primary_key($table);
	$data['updated'] = __db_timestamp();
	$data = db_convert($table, $data);
	$cond = db_convert($table, $cond);
	if (count($cond) == 0) {
		fatal(config('error_invalid_condition'));
	}
	if ($pk !== NULL) {
		unset($data[$pk]);
	}

	// UPDATE文を生成
	$query = 'UPDATE `' . db_escape($table) . '` SET ';
	foreach ($data as $field => $value) {
		$query .= '`' . db_escape($field) . "`='" . db_escape($value) . "',";
	}
	$query = rtrim($query, ',') . ' WHERE ' . __db_where_assoc($cond);

	// クエリを実行
	db_query($query);

	// 影響したIDを返す
	return mysqli_affected_rows(db_connect());
}

/**
 * テーブルを更新する
 *
 * updatedが現在の日時に設定される。
 *
 * @param	string	$table		テーブル名
 * @param	string	$data		更新する値の連想配列
 * @param	string	$cond_value	条件の値
 * @param	string	$cond_field	条件のフィールド名
 * @return	integer	更新されたレコード数
 * @package	db
 */
function db_update_at($table, $data, $cond_value, $cond_field = NULL)
{
	if ($cond_field === NULL) {
		$cond_field = db_primary_key($table);
		if ($cond_field === NULL) {
			fatal(config('error_primary_key'));
		}
	}
	return db_update($table, $data, array($cond_field => $cond_value));
}

/**
 * テーブルを削除する
 *
 * @param	string	$table	テーブル名
 * @param	string	$cond	削除する条件の連想配列
 * @return	integer	削除されたレコード数
 * @package	db
 */
function db_delete($table, $cond)
{
	// データの準備
	$cond = db_convert($table, $cond);
	if (count($cond) == 0) {
		fatal(config('error_invalid_condition'));
	}

	// DELETE文を生成
	$query = 'DELETE FROM `' . db_escape($table) . '` WHERE ';
	$query .= __db_where_assoc($cond);

	// クエリを実行
	db_query($query);

	// 影響したIDを返す
	return mysqli_affected_rows(db_connect());
}

/**
 * テーブルを削除する
 *
 * @param	string	$table		テーブル名
 * @param	string	$cond_value	条件の値
 * @param	string	$cond_field	条件のフィールド名
 * @return	integer	削除されたレコード数
 * @package	db
 */
function db_delete_at($table, $cond_value, $cond_field = NULL)
{
	if ($cond_field === NULL) {
		$cond_field = db_primary_key($table);
		if ($cond_field === NULL) {
			fatal(config('error_primary_key'));
		}
	}
	return db_delete($table, array($cond_field => $cond_value));
}

/**
 * テーブルを挿入・更新する
 *
 * $dataのプライマリキーの値が重複していない場合、挿入処理を行う。
 * 重複している場合、更新処理を行う。
 *
 * updatedが現在の日時に設定される。
 *
 * @param	string	$table		テーブル名
 * @param	string	$data		更新する値の連想配列
 * @return	integer	プライマリキーの値
 * @package	db
 */
function db_replace($table, $data)
{
	// データの準備
	$pk = db_primary_key($table);
	$data['created'] = $data['updated'] = __db_timestamp();
	$data = db_convert($table, $data);

	// INSERT ON DUPLICATE KEY UPDATE文を生成
	if (isset($data[$pk]) && $data[$pk] === '') {
		unset($data[$pk]);
	}
	$query = __db_insert($table, $data);
	unset($data[$pk]);
	unset($data['created']);
	if (count($data) == 0) {
		$query = 'INSERT IGNORE' . substr($query, 6);
	} else {
		$query .= ' ON DUPLICATE KEY UPDATE ';
		foreach ($data as $field => $value) {
			$query .= db_quote_field($field) . "='";
			$query .= db_escape($value) . "',";
		}
		$query = rtrim($query, ',');
	}

	// クエリを実行
	db_query($query);

	// 生成したIDを返す
	return mysqli_insert_id(db_connect());
}

/**
 * MySQLの日時を生成する
 *
 * @param	string	$timestamp	UNIXタイムスタンプ
 * @return	string	MySQLの日時文字列
 * @package	db
 */
function db_datetime($timestamp = NULL)
{
	if ($timestamp === NULL) {
		$timestamp = time();
	}
	return date('Y-m-d H:i:s', $timestamp);
}

/**
 * MySQLの日付を生成する
 *
 * @param	string	$timestamp	UNIXタイムスタンプ
 * @return	string	MySQLの日付文字列
 * @package	db
 */
function db_date($timestamp = NULL)
{
	if ($timestamp === NULL) {
		$timestamp = time();
	}
	return date('Y-m-d', $timestamp);
}

/** @ignore */
function __db_where_assoc($cond)
{
	$query = '1=1';
	foreach ($cond as $field => $value) {
		$query .= ' AND `' . db_escape($field);
		$query .= "`='" . db_escape($value) . "'";
	}
	return $query;
}

/** @ignore */
function __db_insert($table, $data)
{
	$query_field = $query_value = '';
	foreach ($data as $field => $value) {
		$query_field .= '`' . db_escape($field) . '`,';
		$query_value .= "'" . db_escape($value) . "',";
	}
	$query_field = rtrim($query_field, ',');
	$query_value = rtrim($query_value, ',');
	$query = 'INSERT INTO `' . db_escape($table) . '` (';
	$query .= $query_field . ') VALUES (' . $query_value . ')';
	return $query;
}

/** @ignore */
function __db_timestamp()
{
	global $__photon_timestamp;
	if (isset($__photon_timestamp)) {
		return $__photon_timestamp;
	} else {
		return $__photon_timestamp = db_datetime();
	}
}

//------------------------------------------------------------------------------
// ページネーション
//------------------------------------------------------------------------------

/**
 * ページネーションのHTMLを生成する
 *
 * ページネーションの入力値として、下記の連想配列の要素を設定する。
 *
 * |* per_page	| ページ毎のデータ数
 * |* num_links	| 表示するページ番号の数
 * |* first_tag	| 最初に移動するタグ
 * |* prev_tag	| 前に移動するタグ
 * |* link_tag	| ページ番号に移動するタグ
 * |* active_tag| 現在のページのタグ
 * |* next_tag	| 次に移動するタグ
 * |* last_tag	| 最後に移動するタグ
 * |* open_tag	| ページネーションの開始タグ
 * |* close_tag	| ページネーションの終了タグ
 * |* url		| リンク先のURL
 * |* page		| 現在のページ番号
 *
 * config関数で設定した下記の設定オプションが
 * デフォルト値として使用される。
 *
 * - paginate_per_page
 * - paginate_num_links
 * - paginate_first_tag
 * - paginate_prev_tag
 * - paginate_link_tag
 * - paginate_active_tag
 * - paginate_next_tag
 * - paginate_last_tag
 * - paginate_open_tag
 * - paginate_close_tag
 *
 * ページネーションの返り値として、下記の連想配列の要素が設定される
 *
 * |* offset		| 表示開始する行番号
 * |* limit			| 表示する行数
 * |* total_pages	| ページ数
 * |* total_rows	| 行数
 * |* html			| HTMLタグ
 *
 * @param	integer	$total_rows	データ数
 * @param	array	$p			ページネーションの入力値
 * @return	array	ページネーションの出力値
 * @package	paginate
 */
function paginate($total_rows, $p = NULL)
{
	// ページネーションを設定
	if ($p === NULL) {
		$p = array();
	}
	if (!isset($p['per_page'])) {
		$p['per_page'] = config('paginate_per_page');
	}
	if (!isset($p['num_links'])) {
		$p['num_links'] = config('paginate_num_links');
	}
	if (!isset($p['first_tag'])) {
		$p['first_tag'] = config('paginate_first_tag');
	}
	if (!isset($p['prev_tag'])) {
		$p['prev_tag'] = config('paginate_prev_tag');
	}
	if (!isset($p['link_tag'])) {
		$p['link_tag'] = config('paginate_link_tag');
	}
	if (!isset($p['active_tag'])) {
		$p['active_tag'] = config('paginate_active_tag');
	}
	if (!isset($p['next_tag'])) {
		$p['next_tag'] = config('paginate_next_tag');
	}
	if (!isset($p['last_tag'])) {
		$p['last_tag'] = config('paginate_last_tag');
	}
	if (!isset($p['open_tag'])) {
		$p['open_tag'] = config('paginate_open_tag');
	}
	if (!isset($p['close_tag'])) {
		$p['close_tag'] = config('paginate_close_tag');
	}
	if (!isset($p['url'])) {
		$p['url'] = get_request_url();
	}
	if (!isset($p['page'])) {
		if (isset($_REQUEST['page'])) {
			$p['page'] = $_REQUEST['page'];
		} else {
			$p['page'] = 1;
		}
	}

	// ページ数
	$total_pages = max(ceil($total_rows / $p['per_page']), 1);

	// ページ番号を範囲内に収める
	$p['page'] = limit_range($p['page'], 1, $total_pages);

	// 行の範囲を求める
	$p['offset'] = ($p['page'] - 1) * $p['per_page'];
	$p['limit'] = min($total_rows - $p['offset'], $p['per_page']);

	// ページ番号の範囲を求める
	$page_start = max($p['page'] - floor($p['num_links'] / 2), 1);
	$page_end = min($page_start + $p['num_links'] - 1, $total_pages);
	$page_start = min($page_start, max(1, $page_end - $p['num_links'] + 1));

	// ページ数と行数を返り値に含める
	$p['total_pages'] = $total_pages;
	$p['total_rows'] = $total_rows;

	// HTMLを生成する
	$html = '';
	if ($p['page'] != 1) {
		$html .= __paginate_tag(1, $p['url'], $p['first_tag']);
		$html .= __paginate_tag($p['page'] - 1, $p['url'], $p['prev_tag']);
	}
	for ($page = $page_start; $page <= $page_end; $page++) {
		if ($p['page'] == $page) {
			$html .= __paginate_tag($page, $p['url'], $p['active_tag']);
		} else {
			$html .= __paginate_tag($page, $p['url'], $p['link_tag']);
		}
	}
	if ($p['page'] != $page_end) {
		$html .= __paginate_tag($p['page'] + 1, $p['url'], $p['next_tag']);
		$html .= __paginate_tag($total_pages, $p['url'], $p['last_tag']);
	}
	$p['html'] = $p['open_tag'] . $html . $p['close_tag'];

	return $p;
}

/** @ignore */
function __paginate_tag($page, $url, $tag)
{
	$vars = array();
	$vars['page'] = $page;
	$vars['url'] = modify_url_query($url, array('page' => $page));
	return embed($tag, $vars);
}

/**
 * クエリを実行し、ページネーションのHTMLを生成する
 *
 * ページネーションの入力値として、下記の連想配列の要素を設定する
 *
 * |* query_count	| データ数を求めるクエリ文
 *
 * ページネーションの返り値として、下記の連想配列の要素が設定される
 *
 * |* records	| 取得したレコードの配列
 * |* count		| データ数
 *
 * @param	string	$query	クエリ文
 * @param	array	$p		ページネーションの入力値
 * @return	array	ページネーションの出力値
 * @package	paginate
 */
function db_paginate($query, $p = NULL)
{
	// データ数を求める
	if (isset($p['query_count'])) {
		$query_count = $p['query_count'];
	} else {
		$query_count = 'SELECT COUNT(*) FROM (' . $query . ') AS __db_paginate';
	}
	$p['count'] = db_select_value($query_count);

	// ページネーションのHTMLを生成する
	$p = paginate($p['count'], $p);

	// レコードを取得する
	$query .= ' LIMIT ' . $p['offset'] . ',' . $p['limit'];
	$p['records'] = db_select_table($query);

	return $p;
}

//------------------------------------------------------------------------------
// SQLクエリ
//------------------------------------------------------------------------------

/**
 * SQLクエリ文の生成を開始する
 *
 * 現在の設定をスタックに保存する。
 * SQLクエリ文の生成条件はスタック可能であり、sql_beginは何度も呼び出せる。
 *
 * @package	sql
 */
function sql_push()
{
	global $__photon_sql;
	global $__photon_sql_stack;

	if (!isset($__photon_sql_stack)) {
		$__photon_sql_stack = array();
	}
	array_push($__photon_sql_stack, $__photon_sql);
	sql_clean();
}

/**
 * SQLクエリ文の生成を終了する
 *
 * 最後にsql_beginを呼んだ際のSQLクエリ文の生成条件に復帰する。
 *
 * @package	sql
 */
function sql_pop()
{
	global $__photon_sql;
	global $__photon_sql_stack;

	$__photon_sql = array_pop($__photon_sql_stack);
}

/**
 * 処理対象のテーブル名を設定する
 *
 * @param	string	$table	テーブル名
 * @param	string	$alias	エイリアス名
 * @package	sql
 */
function sql_table($table, $alias = NULL)
{
	global $__photon_sql;

	$__photon_sql['table'] = $table;
	$__photon_sql['alias'] = $alias;
}

/**
 * SQLクエリ文の生成条件をクリアする
 *
 * @package	sql
 */
function sql_clean()
{
	global $__photon_sql;

	$__photon_sql = array();
}

/**
 * SELECT文を生成する
 *
 * 下記のSQL文の生成条件が有効となる。
 * - sql_field
 * - sql_join
 * - sql_where
 * - sql_group
 * - sql_order
 * - sql_limit
 *
 * @param	string	$table	テーブル名
 * @param	string	$alias	エイリアス名
 * @return	string	生成されたSQL文字列
 * @package	sql
 */
function sql_select($table = NULL, $alias = NULL)
{
	global $__photon_sql;

	if ($table === NULL) {
		$table = $__photon_sql['table'];
	}
	if ($alias === NULL) {
		$alias = $__photon_sql['alias'];
	}
	$query = 'SELECT ';
	if (isset($__photon_sql['field'])) {
		$query .= rtrim($__photon_sql['field'], ',');
	} else {
		$query .= '`' . db_escape($table) . '`.*';
	}
	$query .= ' FROM `' . db_escape($table) . '`';
	if ($alias !== NULL) {
		$query .= ' AS `' . db_escape($alias) . '`';
	}
	if (isset($__photon_sql['join'])) {
		$query .= ' ' . $__photon_sql['join'];
	}
	if (isset($__photon_sql['where'])) {
		$query .= ' WHERE ' . $__photon_sql['where'];
	}
	if (isset($__photon_sql['group'])) {
		$query .= ' GROUP BY ' . $__photon_sql['group'];
	}
	if (isset($__photon_sql['order'])) {
		$query .= ' ORDER BY ' . rtrim($__photon_sql['order'], ',');
	}
	if (isset($__photon_sql['limit'])) {
		$query .= ' LIMIT ' . $__photon_sql['limit'];
	}
	return $query;
}

/**
 * INSERT文を生成する
 *
 * 下記のSQL文の生成条件が有効となる。
 * - sql_value
 *
 * @param	string	$table	テーブル名
 * @param	string	$ignore	キーが重複する際に無視する場合はTRUE
 * @return	string	生成されたSQL文字列
 * @package	sql
 */
function sql_insert($table = NULL, $ignore = FALSE)
{
	global $__photon_sql;

	if ($table === NULL) {
		$table = $__photon_sql['table'];
	}
	if ($ignore) {
		$query = 'INSERT IGNORE INTO ';
	} else {
		$query = 'INSERT INTO ';
	}
	$query .= '`' . db_escape($table) . '` (';
	if (isset($__photon_sql['insert_field'])) {
		$query .= rtrim($__photon_sql['insert_field'], ',');
	}
	$query .= ') VALUES (';
	if (isset($__photon_sql['insert_value'])) {
		$query .= rtrim($__photon_sql['insert_value'], ',');
	}
	$query .= ')';
	return $query;
}

/**
 * UDPATE文を生成する
 *
 * 下記のSQL文の生成条件が有効となる。
 * - sql_set
 * - sql_where
 *
 * @param	string	$table	テーブル名
 * @return	string	生成されたSQL文字列
 * @package	sql
 */
function sql_update($table = NULL)
{
	global $__photon_sql;

	if ($table === NULL) {
		$table = $__photon_sql['table'];
	}
	$query = 'UPDATE `' . db_escape($table) . '` SET ';
	if (isset($__photon_sql['set'])) {
		$query .= rtrim($__photon_sql['set'], ',');
	}
	if (isset($__photon_sql['where'])) {
		$query .= ' WHERE ' . $__photon_sql['where'];
	}
	return $query;
}

/**
 * DELETE文を生成する
 *
 * 下記のSQL文の生成条件が有効となる。
 * - sql_where
 *
 * @param	string	$table	テーブル名
 * @return	string	生成されたSQL文字列
 * @package	sql
 */
function sql_delete($table = NULL)
{
	global $__photon_sql;

	$query = 'DELETE FROM `' . db_escape($table) . '`';
	if (isset($__photon_sql['where'])) {
		$query .= ' WHERE ' . $__photon_sql['where'];
	}
	return $query;
}

/**
 * SELECT文で取得するフィールドを追加する
 *
 * $fieldにはdb_quote_fieldでの指定形式が使用できる。
 * フィールド名単体、テーブル名とフィールド名の指定、式の指定ができる。
 *
 * @param	string	$field	取得するフィールド
 * @param	string	$alias	エイリアス名
 * @package	sql
 */
function sql_field($field, $alias = NULL)
{
	$str = db_quote_field($field);
	if ($alias !== NULL) {
		$str .= ' AS `' . db_escape($alias) . '`';
	}
	$str .= ',';
	__sql_append('field', $str);
}

/**
 * 他テーブルとの結合条件を追加する
 *
 * 下記の形式で結合条件が追加される。
 * LEFT JOIN `$join_fieldのテーブル名` ON $join_field=$cond_field
 *
 * $cond_fieldにはdb_quote_fieldでの指定形式が使用できる。
 * フィールド名単体、テーブル名とフィールド名の指定、式の指定ができる。
 *
 * @param	string	$table		結合先のテーブル名
 * @param	string	$field		結合先のフィールド名
 * @param	string	$cond_table	条件のテーブル名
 * @param	string	$cond_field	条件のフィールド名
 * @param	string	$alias		結合するテーブルのエイリアス名
 * @package	sql
 */
function sql_join($table, $field, $cond_table, $cond_field, $alias = NULL)
{
	$str = 'LEFT JOIN `' . db_escape($table) . '`';
	if ($alias !== NULL) {
		$str .= ' AS `' . db_escape($alias) . '`';
		$table = $alias;
	}
	$str .= ' ON `' . db_escape($table) . '`.`' . db_escape($field) . '`=`';
	$str .= db_escape($cond_table) . '`.`' . db_escape($cond_field) . '`';
	__sql_append('join', $str);
}

/**
 * WHERE節の指定を開始する
 *
 * 通常、sql_where系で条件を指定する場合AND条件となるが、一部をOR条件等で
 * 結合する場合、sql_where_beginで論理演算子を指定し、sql_where_endで終了する。
 *
 * WHERE条件はスタック可能であり、sql_where_beginは何度も呼び出せる。
 *
 * @param	string	$glue	条件を結合する論理演算子
 * @package	sql
 */
function sql_where_begin($glue)
{
	global $__photon_sql;

	// 論理演算子をスタックに保存
	if (!isset($__photon_sql['where_stack'])) {
		$__photon_sql['where_stack'] = array();
	}
	if (isset($__photon_sql['where_glue'])) {
		array_push($__photon_sql['where_stack'], $__photon_sql['where_glue']);
	} else {
		array_push($__photon_sql['where_stack'], 'AND');
	}

	// WHERE節に追加
	$glue = strtoupper($glue);
	if ($glue === 'AND') {
		sql_where('(1=1');
	} else {
		sql_where('(1=0');
	}

	// 論理演算子を設定
	$__photon_sql['where_glue'] = ' ' . $glue . ' ';
}

/**
 * WHERE節の指定を開始する
 *
 * 前にsql_where_beginを呼び出した際の論理演算子に復帰する。
 * @package	sql
 */
function sql_where_end()
{
	global $__photon_sql;

	// 論理演算子を復帰
	$__photon_sql['where_glue'] = array_pop($__photon_sql['where_stack']);

	// WHERE節に追加
	__sql_append('where', ')');
}

/**
 * WHERE節に条件文を加える
 *
 * @param	string	$expr	条件を表す文字列
 * @package	sql
 */
function sql_where($expr)
{
	global $__photon_sql;

	if (!isset($__photon_sql['where_glue'])) {
		__sql_append('where', '1=1 AND ' . $expr);
		$__photon_sql['where_glue'] = ' AND ';
	} else {
		__sql_append('where', $__photon_sql['where_glue'] . $expr);
	}
}

/**
 * 文字列との比較をWHERE節に追加する
 *
 * @param	string	$field		フィールドの条件 (db_quote_fieldの形式)
 * @param	string	$value		比較する値
 * @param	string	$operator	比較演算子
 * @package	sql
 */
function sql_where_string($field, $value, $operator = '=')
{
	$str = db_quote_field($field) . $operator . "'" . db_escape($value) . "'";
	sql_where($str);
}

/**
 * 整数値との比較をWHERE節に追加する
 *
 * @param	string	$field		フィールドの条件 (db_quote_fieldの形式)
 * @param	integer	$value		比較する値
 * @param	string	$operator	比較演算子
 * @package	sql
 */
function sql_where_integer($field, $value, $operator = '=')
{
	$str = db_quote_field($field) . $operator . intval($value);
	sql_where($str);
}

/**
 * 範囲条件をWHERE節に追加する
 *
 * @param	string	$field	フィールドの条件 (db_quote_fieldの形式)
 * @param	string	$min	下限値
 * @param	string	$max	上限値
 * @package	sql
 */
function sql_where_between($field, $min, $max)
{
	$str = db_quote_field($field) . " BETWEEN '";
	$str .= db_escape($min) . "' AND '" . db_escape($max) . "'";
	sql_where($str);
}

/**
 * キーワード検索をWHERE節に追加する
 *
 * @param	string	$fields		対象となるフィールド、またはフィールドの配列
 * @param	string	$keywords	空白で区切られた検索キーワード
 * @package	sql
 */
function sql_where_search($fields, $keywords)
{
	if (!is_array($fields)) {
		$fields = array($fields);
	}
	if (!is_array($keywords)) {
		$keywords = explode(' ', mb_convert_kana($keywords, 's'));
	}
	$str = '(1=1';
	foreach ($keywords as $keyword) {
		$keyword = addcslashes($keyword, '%_');
		$str .= ' AND (1=0';
		foreach ($fields as $field) {
			if ($keyword === '' || $keyword === NULL) {
				continue;
			}
			$str .= ' OR ' . db_quote_field($field) . ' LIKE ';
			$str .= "'%" . db_escape($keyword) . "%'";
		}
		$str .= ')';
	}
	$str .= ')';
	sql_where($str);
}

/**
 * GROUP BY節を設定する
 *
 * @param	string	$field	グループ化するフィールド名
 * @package	sql
 */
function sql_group($field)
{
	global $__photon_sql;

	$__photon_sql['group'] = db_quote_field($field);
}

/**
 * ORDER BY節を追加する
 *
 * @param	string	$field		ソートするフィールド名
 * @param	string	$ascending	昇順ならTRUE、降順ならFALSE
 * @package	sql
 */
function sql_order($field, $ascending = TRUE)
{
	global $__photon_sql;

	$str = db_quote_field($field);
	if ($ascending === FALSE) {
		$str .= ' DESC';
	}
	$str .= ',';
	__sql_append('order', $str);
}

/**
 * LIMIT節を設定する
 *
 * @param	integer	$offset	取得開始する行番号
 * @param	integer	$limit	取得するレコード数
 * @package	sql
 */
function sql_limit($offset, $limit)
{
	global $__photon_sql;

	$__photon_sql['limit'] = intval($offset) . ',' . intval($limit);
}

/**
 * INSERT文のフィールド名と値を設定する
 *
 * @param	string	$field	挿入するレコードのフィールド名
 * @param	string	$value	挿入するレコードの値
 * @package	sql
 */
function sql_value($field, $value)
{
	__sql_append('insert_field', '`' . db_escape($field) . '`,');
	__sql_append('insert_value', "'" . db_escape($value) . "',");
}

/**
 * UPDATE文のSET節に追加する
 *
 * @param	string	$field	フィールド名
 * @param	string	$expr	更新する式
 * @package	sql
 */
function sql_set($field, $expr)
{
	__sql_append('set', '`' . db_escape($field) . '`=' . $expr . ',');
}

/**
 * 文字列による更新をSET節に追加する
 *
 * @param	string	$field	フィールド名
 * @param	string	$value	更新する値
 * @package	sql
 */
function sql_set_string($field, $value)
{
	sql_set($field, "'" . db_escape($value) . "'");
}

/**
 * 整数値による更新をSET節に追加する
 *
 * @param	string	$field	フィールド名
 * @param	integer	$value	更新する値
 * @package	sql
 */
function sql_set_integer($field, $value)
{
	sql_set($field, intval($value));
}

/** @ignore */
function __sql_append($key, $str)
{
	global $__photon_sql;

	if (!isset($__photon_sql[$key])) {
		$__photon_sql[$key] = '';
	}
	$__photon_sql[$key] .= $str;
}

//------------------------------------------------------------------------------
// メール
//------------------------------------------------------------------------------

/**
 * メールを送信する
 *
 * メールログの設定がある場合には、ログに記録する。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* log_mail	| メール送信履歴のテーブル名。空文字列で無効化
 *
 * @param	string	$to			送信先メールアドレス
 * @param	string	$from		送信元メールアドレス
 * @param	string	$subject	メールの題名
 * @param	string	$message	メールの本文
 * @return	boolean	成功した場合にはTRUE、失敗した場合にはFALSE
 * @package	sendmail
 */
function sendmail($to, $from, $subject, $message)
{
	// 改行コードを変換
	$to = convert_newline($to, '');
	$from = convert_newline($from, '');
	$subject = convert_newline($subject, '');
	$message = convert_newline($message);

	// メールの送信
	mb_language("uni");
	mb_internal_encoding("utf-8");
	mb_send_mail($to, $subject, $message, 'From: ' . $from);

	// メール送信履歴に記録
	$log_mail = config('log_mail');
	if ($log_mail !== '') {
		db_insert($log_mail, compact('to', 'from', 'subject', 'message'));
	}
}

//------------------------------------------------------------------------------
// 認証
//------------------------------------------------------------------------------

/**
 * 認証領域を定義し、現在の認証領域に設定する
 *
 * @param    string $realm 認証領域の名前
 * @param    string $url 認証するためのログインURL
 * @package    auth
 */
function auth_realm($realm, $url)
{
	global $__photon_realm;
	global $__photon_url;
	global $__photon_id;

	$__photon_realm = $realm;
	$__photon_url = $url;

	// 復号化
	$key = config('secret_key');
	if (isset($_COOKIE[$realm])) {
		$iv = substr(sha1($key), 0, 16);
		$str = base64_decode($_COOKIE[$realm]);
		$str = openssl_decrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
	} else {
		$str = NULL;
	}

	// ユーザ識別子を設定
	$__photon_id = '';
	if ($str !== NULL) {
		list($realm_check, $id, $time,) = unserialize($str);
		if ($realm_check === $realm && is_numeric($time)) {
			$time = intval($time);
			$expire = $time + config('auth_expire');
			if (time() < $expire) {
				$__photon_id = $id;
				auth_login($id);
			}
		}
	}
}

/**
 * 現在の認証領域にログインする
 *
 * @param    integer $id ユーザの識別子 (0以外の値)
 * @package    auth
 */
function auth_login($id)
{
	global $__photon_id;
	global $__photon_realm;

	if (!isset($__photon_realm)) {
		$__photon_realm = config('auth_realm');
	}

	$__photon_id = $id;

	// 暗号化
	$key = config('secret_key');
	$random = openssl_random_pseudo_bytes(256);
	$iv = substr(sha1($key), 0, 16);
	$str = serialize(array($__photon_realm, $id, time(), $random));
	$str = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
	$str = base64_encode($str);
	setcookie($__photon_realm, $str);
}

/**
 * 現在の認証領域からログアウトする
 *
 * @package	auth
 */
function auth_logout()
{
	auth_login('');
}

/**
 * 認証を要求する
 *
 * 現在の認証領域でログインしていない場合には、auth_realmで設定した
 * ログインURLにリダイレクトし、強制終了する。
 *
 * @package	auth
 */
function auth_require()
{
	global $__photon_url;
	if (auth_id() === '') {
		redirect($__photon_url);
		exit;
	}
}

/**
 * ユーザの識別子を取得する
 *
 * @return	integer	ユーザの識別子。ログインしていない場合には、0を返す。
 * @package	auth
 */
function auth_id()
{
	global $__photon_id;
	if (!isset($__photon_id)) {
		// 認証領域が設定されていない場合の処理
		auth_realm(config('auth_realm'), NULL);
	}
	return $__photon_id;
}

//------------------------------------------------------------------------------
// ビューとコントローラ
//------------------------------------------------------------------------------

/**
 * ブラウザをリダイレクトする
 *
 * @param	string	$url	リダイレクト先のURL
 * @package	controller
 */
function redirect($url)
{
	header('Location: ' . relative_url($url, get_request_url()));
	exit;
}

/**
 * コンテンツをダウンロードさせる
 *
 * @param   string  $filename   ファイル名
 * @param   string  $content    コンテンツ
 * @param   string  $type       MIMEタイプ
 */
function download($filename, $content, $type = 'application/octet-stream')
{
	header('Content-Type: ' . $type);
	header('Content-Length: ' . strlen($content));
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	echo $content;
}

/**
 * 致命的エラーの発生を通知する
 *
 * エラーメッセージを表示して、強制終了する。
 *
 * config関数で設定した下記の設定オプションが使用される。
 *
 * |* log_error		| エラーログのテーブル名。空文字列で無効化
 * |* error_title	| エラーページのタイトル
 *
 * @param	string	$message	エラーメッセージ
 * @param	string	$severity	重要度
 * @package	controller
 */
function fatal($message, $severity = 'fatal')
{
	global $__photon_log_error;

	// トランザクションを終了する
	db_rollback();

	// 出力バッファをクリア
	ob_end_clean();

	// エラー履歴を記録
	$log_error = config('log_error');
	if ($log_error !== '') {
		if (!isset($__photon_log_error)) {
			$__photon_log_error = TRUE;
			$url = get_request_url();
			db_insert($log_error, compact('severity', 'url', 'message'));
		}
	}

	// エラーメッセージをブラウザに出力
	if (is_true(ini_get('display_errors'))) {
		$str = '<html>';
		$str .= '<head>';
		$str .= '<meta http-equiv="content-type" ';
		$str .= 'content="text/html;charset=utf-8">';
		$str .= '<title>' . config('error_title') . '</title>';
		$str .= '</head>';
		$str .= '<body>';
		$str .= '<h1>' . config('error_title') . '</h1>';
		$str .= '<p>' . htmlspecialchars($message) . '</p>';
		$str .= '</body></html>';
		die($str);
	} else {
		die();
	}
}

/**
 * ビューのフィルタ関数を追加する
 *
 * render関数の出力に対するフィルタ関数を登録する。
 *
 * フィルタ関数は、第一引数が出力の文字列、第二引数が$argとなり、
 * 変換結果を返り値とするものである。
 *
 * フィルタ関数はチェイン処理され、
 * ビュー評価の際に最初に登録したものから順番に実行される。
 *
 * @param	string	$function	ビュー出力のフィルタ関数
 * @param	mixed	$arg		フィルタ関数の第二引数
 * @package	controller
 */
function add_filter($function, $arg = NULL)
{
	global $__photon_filter;
	$__photon_filter[] = array($function, $arg);
}

/**
 * ビューを評価し、ブラウザに出力する
 *
 * $returnがTRUEの場合、ビューを出力せずに返り値として返す。
 * ビューを評価する際、$dataの変数が展開される。
 *
 * @param	string	$filename	ビューファイルのファイル名
 * @param	array	$data		ビューに渡す変数
 * @param	boolean	$return		出力せずに返り値とするならTRUE
 * @return	string	$returnがTRUEの場合にはビューファイルの評価結果
 * @see		add_filter
 * @package	controller
 */
function render($filename, $data = array(), $return = FALSE)
{
	global $__photon_filter;

	// ビューファイルの相対位置指定を解決する
	$filename = relative_path($filename, config('view_dir'));

	// ビューファイルの存在を確認する
	if (!file_exists($filename)) {
		fatal(config('error_view_not_found') . "\n" . $filename);
	}

	// 変数を退避する
	$__filename = $filename;
	$__data = $data;
	$__return = $return;
	$__ob = $return || count($__photon_filter);
	unset($data['__filename']);
	unset($data['__data']);
	unset($data['__return']);
	unset($data['__ob']);
	unset($data['__photon_filter']);

	if (!$__ob) {
		// ビューファイルを出力する
		extract($__data);
		include($__filename);
		return NULL;
	}

	// ビューファイルを評価する
	ob_start();
	extract($__data);
	include($__filename);
	$contents = ob_get_contents();
	ob_end_clean();

	// ビューファイルの評価結果をフィルタ処理を行う
	foreach ($__photon_filter as $filter) {
		list($function, $arg) = $filter;
		$contents = call_user_func($function, $contents, $arg);
	}

	if (!$__return) {
		// 評価結果を出力する
		echo $__return;
		return NULL;
	}

	// 評価結果を返す
	return $__return;
}

/**
 * アクション関数を追加する
 *
 * @param	string	$action		アクション名
 * @param	string	$function	アクション関数
 * @param	mixed	$arg		アクション関数の第二引数
 * @package	controller
 */
function add_action($action, $function, $arg = NULL)
{
	global $__photon_action;
	$__photon_action[$action] = array($function, $arg);
}

/**
 * 現在のアクションを取得する
 *
 * @param	array	$data	アクション関数のパラメータ
 * @return	mixed	アクション名
 * @package	controller
 */
function get_action($data = NULL)
{
	// $dataが省略された場合、$_REQUESTをパラメータとする
	if ($data === NULL) {
		$data = $_REQUEST;
	}

	// アクションを取得する
	if (isset($data['action'])) {
		if (is_array($data['action'])) {
			return current(array_keys($data['action']));
		} else {
			return $data['action'];
		}
	} else {
		return 'index';
	}
}

/**
 * アクションを実行する
 *
 * 通常は引数なしで呼び出し、GETやPOSTのactionパラメータのアクションを実行する。
 *
 * action[アクション名]といった名前のボタンを作ることで、
 * 複数のボタンでアクションを切り分ける事ができる。
 *
 * $actionや$dataを指定することで、アクション内で別のアクションを呼び出すことも
 * できる。
 *
 * @param	string	$action		アクション名
 * @param	mixed	$data		アクション関数のパラメータ
 * @return	mixed	アクション関数の返り値
 * @see		add_action
 * @package	controller
 */
function execute($action = NULL, $data = NULL)
{
	global $__photon_action;

	// $dataが省略された場合、$_REQUESTをパラメータとする
	if ($data === NULL) {
		$data = $_REQUEST;
	}

	// $actionが省略された場合、$dataからアクション名を決定する
	if ($action === NULL) {
		$action = get_action($data);
	}

	// アクションがadd_actionで追加されていた場合の処理
	if (isset($__photon_action[$action])) {
		list($function, $arg) = $__photon_action[$action];
		return call_user_func($function, $data, $arg);
	}

	// アクション関数が存在する場合
	$function = 'action_' . $action;
	if (function_exists($function)) {
		return call_user_func($function, $data);
	}

	// アクション関数が存在しない
	fatal(config('error_action_not_found') . "\n" . $action);
}

/** @ignore */
function __photon_init()
{
	// PHPのバージョンを調べる
	if (version_compare(PHP_VERSION, '5.1', '<')) {
		die('This application requires at least PHP version 5.1');
	}

	// PHPの拡張を調べる
	foreach (array('gd', 'mcrypt', 'mbstring', 'mysqli') as $extension) {
		if (!extension_loaded($extension)) {
			die('This application requires extension: ' . $extension);
		}
	}

	// secret_keyが設定されているか調べる
	if (config('secret_key') == 'SECRET_KEY') {
		die('The configuration file now needs a secret passphrase');
	}

	// mbstringの設定
	mb_language("Japanese");
	mb_internal_encoding("utf-8");
	mb_regex_encoding("utf-8");

	// ブラウザを閉じても実行する
	ignore_user_abort();
}

?>
