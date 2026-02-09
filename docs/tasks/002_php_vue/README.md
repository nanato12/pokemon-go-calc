# 共有サーバー × Laravel × Next / Vue / React 設計整理メモ

## 前提条件

- 共有サーバーを使用
- PHPは通常利用可能
- Node.js 実行環境はある（build等は可能）
- ただし Node の常駐プロセス（next start / SSR常駐）は不可
- SEO重視（初回HTMLが重要）

---

## 基本認識

### PHP（Laravel）

- リクエスト駆動
- 常駐不要
- 共有サーバー適正あり
- Blade によりサーバーサイドで HTML を完成させられる（＝SSR）

### Node / Next.js

- SSRには Node の常駐が必須
- 共有サーバーでは常駐不可のため、Next SSRは不可
- next build / next export のような「一回実行して終わる処理」は可能

---

## Next.js を共有サーバーで使えるか？

### ❌ 不可

- Next.js SSR（getServerSideProps / App Router SSR）
- API Routes
- Route Handlers
- ISR（オンデマンド再生成）

→ Node 常駐が必要なため

### ✅ 可（制限付き）

- Next SSG（静的出力）
- build → export した成果物を静的ホスティング

※ ただし SEOの柔軟性・更新性は弱い

---

## SEO重視 × 共有サーバーの現実解

### Laravel SSR（Blade）を主軸にする

- SEOに必要なHTMLは Laravel が生成
- title / meta / OGP / 本文 / 一覧 / 詳細は Blade で出す
- Googleに「最初から完成HTML」を返す

---

## Vue / React は使えるのか？

### 結論：使える（しかも公式想定）

- LaravelがHTMLを完成させる（SSR）
- Vue / React は「後から部分的にマウント」
- Node常駐は不要
- Nodeは「ビルド用途のみ」でOK

これは **Laravel SSR + Islands Architecture（擬似）**

---

## 正しい構成（重要）

### Blade（Laravel側）

- SEO対象コンテンツをすべてHTMLとして出す
- h1 / 本文 / 一覧 / meta は Blade
- Vue/React 用の mount point を data属性付きで用意

例：

```html
<h1>{{ $job->title }}</h1>
<p>{{ $job->description }}</p>

<div id="favorite"
     data-job-id="{{ $job->id }}"
     data-liked="{{ $liked ? 'true' : 'false' }}">
</div>
```

---

### Vue / React（フロント側）

- 上記要素に対して mount
- UI / 状態管理 / インタラクションを担当
- 型安全は TS 側で担保

例（Vue）：

```ts
createApp(FavoriteButton, {
  jobId: Number(el.dataset.jobId),
  liked: el.dataset.liked === 'true',
}).mount(el)
```

---

## やってはいけない構成（SEO死亡）

- Blade が `<div id="app"></div>` だけ
- 中身を Vue / React が API 経由で描画

→ これは SPA  
→ 初回HTMLが空  
→ SEO弱い・不安定

---

## Node.js の役割（この構成での正解）

- Vite / Webpack によるビルド専用
- `npm run build` して終わり
- サーバーとしては一切使わない

---

## まとめ（結論）

- 共有サーバー × SEO重視 → Laravel SSR が必然
- Next.js SSR は常駐不可のため不適
- Laravel SSR に Vue / React を「部分的に」埋め込むのが最適解
- Bladeに型を求めない
- 型の主戦場は TypeScript 側に寄せる
