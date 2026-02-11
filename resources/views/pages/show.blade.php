@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-[#002a1c] p-6 lg:p-10">
    <div class="max-w-4xl mx-auto bg-white dark:bg-surface-dark rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 lg:p-12">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">{{ $page->title }}</h1>
        
        <div class="prose dark:prose-invert max-w-none">
            {!! $page->content !!}
        </div>
        
        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400 text-center">
            Terakhir diperbarui: {{ $page->updated_at->format('d M Y H:i') }}
        </div>
    </div>
</div>
@endsection

