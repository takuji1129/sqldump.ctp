### sqldump.ctp — SQL Log Viewer for CakePHP

**概要**: `sqldump.ctp` は CakePHP のデバッグ用エレメントで、各データソースの SQL ログを一覧表示します。クエリ種別ごとのハイライト、エラー件数表示、所要時間や件数などを見やすく可視化し、jQuery によるクエリ種別フィルタ (ALL/SELECT/UPDATE/INSERT/DELETE) を提供します。

- **対象**: CakePHP アプリの開発時デバッグ
- **主機能**:
  - データソースごとのクエリ回数・総実行時間の集計表示
  - 各クエリの種別ハイライト (SELECT/UPDATE/INSERT/DELETE/SHOW)
  - SQL キーワード/関数/スキーマ名/文字列のハイライト
  - 影響行数・取得行数・処理時間・エラーメッセージの表示
  - エラー合計件数のアラート表示
  - jQuery によるクエリ種別フィルタリングボタン

### 要件
- CakePHP (ConnectionManager と Configure が利用可能な環境)
- `Configure::read('debug') >= 2` のデバッグモード
- 各接続ドライバが `getLog` インターフェースに対応していること
- 任意: 画面内フィルタ機能に jQuery を使用 (jQuery が無い場合も一覧表示自体は可能)

### 動作概要
`ConnectionManager::sourceList()` で全データソースを取得し、`getDataSource($source)` 経由で `getLog()` を呼び出して SQL ログを回収します。得られた `count`/`time`/`log` を元に、テーブルで以下を出力します。

- **ヘッダ**: データソース名、クエリ総数、総所要時間 (ms)
- **各行**: 連番、整形済み SQL、エラー内容、影響行数、結果行数、処理時間 (ms)
- **装飾**: UPDATE/INSERT/DELETE/SHOW の行背景、エラー行の強調、SQL キーワード/関数/文字列/スキーマの色分け
- **フィルタ**: ALL / SELECT / UPDATE / INSERT / DELETE の各ボタンで対象行のみ表示

### インストール
1. `app/views/elements/` (CakePHP 1.x/2.x 系の配置先を想定) に `sqldump.ctp` を配置します。
2. ビューやレイアウトの好きな位置で、このエレメントを呼び出します。

```php
// 例: ビュー(.ctp)内
if (Configure::read('debug') >= 2) {
	echo $this->element('sqldump');
}
```

> 注: CakePHP のバージョンによってエレメント呼び出し方法が多少異なる場合があります。`$this->element('sqldump')`、または `echo $this->element('sqldump');` の形式を使用してください。

### 使い方
- デバッグモード (debug >= 2) のときにページ下部などへ SQL ログが表示されます。
- 上部のボタンでクエリ種別を切り替えて絞り込み可能です。
  - ALL QUERY: 全件表示
  - SELECT/UPDATE/INSERT/DELETE: 対応する種別のみ表示
- エラーが検出された場合は、エラー件数が赤いアラートで表示されます。

### 表示例
```text
( default ) 12 queries took 45 ms
Nr  Query                              Error  Affected  Num. rows  Took (ms)
1   SELECT ... FROM ... WHERE ...             0         10         3
2   UPDATE ... SET ... WHERE ...              1         1          12
...
```

- UPDATE/INSERT/DELETE は背景色で強調表示されます。
- SQL 内の `SELECT`, `UPDATE`, `WHERE`, `GROUP BY`, `ORDER BY`, `LIMIT` などはキーワードとして太字・色分けされます。
- 文字列リテラルやバッククォートで囲まれたスキーマ名も色分けされます。

### カスタマイズ
- スタイル: `sqldump.ctp` 内の `<style>` セクションを編集して色や書体を変更可能です。
- フィルタ: `<script>` の jQuery ハンドラでボタンや条件を追加/変更できます。
- ハイライト: `preg_replace` の正規表現を編集することで、追加キーワードや関数の装飾を拡張できます。

### 制限事項 / 注意点
- 本エレメントは開発時専用です。必ず本番環境では `debug < 2` として無効化してください。
- すべてのドライバが `getLog` を実装しているわけではありません。未対応ドライバは一覧からスキップされます。
- jQuery が無い場合、フィルタボタンによる動的絞り込みは動作しません (一覧自体は表示)。

### トラブルシュート
- 何も表示されない
  - `Configure::read('debug') >= 2` か確認
  - 対象データソースのドライバが `getLog` を実装しているか確認
- 種別ボタンが効かない
  - jQuery がページに読み込まれているか確認
- エラー件数が表示される
  - 各行の Error 列を確認し、該当クエリを修正

### ライセンス
このファイルのライセンスが未明示の場合、プロジェクトのルートライセンス方針に従ってください。