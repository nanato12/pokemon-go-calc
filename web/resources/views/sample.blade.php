@extends('layouts.app')

@section('title', 'サンプルページ - GO Pilot')

@section('meta')
    <meta name="description" content="Islands Architecture サンプルページ">
@endsection

@section('content')
<div class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-4xl mx-auto px-4">
        {{-- SEO対象: サーバーサイドレンダリング --}}
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            GO Pilot サンプルページ
        </h1>

        <p class="text-gray-600 mb-8">
            このページは Laravel Blade でサーバーサイドレンダリングされています。
            SEOに重要なコンテンツはすべてHTMLとして出力されます。
        </p>

        {{-- インタラクティブ部分: Vue コンポーネント --}}
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">カウンター (Vue Component)</h2>
            <p class="text-gray-500 mb-4">
                以下はVueコンポーネントとして動的にマウントされます。
            </p>

            {{-- Islands Architecture: data属性でpropsを渡す --}}
            <div
                data-vue-component="Counter"
                data-initial-count="10"
            ></div>
        </div>

        {{-- SEO対象: 追加コンテンツ --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">構成説明</h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>Blade: SEO対象のHTMLを生成</li>
                <li>Vue: インタラクティブな部分のみマウント</li>
                <li>data-vue-component: マウント対象を指定</li>
                <li>data-*: propsとして渡される</li>
            </ul>
        </div>
    </div>
</div>
@endsection
