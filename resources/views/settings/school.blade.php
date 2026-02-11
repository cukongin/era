@extends('layouts.app')

@section('title', 'Identitas Madrasah')

@section('content')
<div class="max-w-4xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Identitas Madrasah</h1>
        <p class="text-slate-500">
            Kelola data identitas untuk MI dan MTs secara terpisah. Data ini digunakan untuk Kop Surat Rapor.
        </p>
    </div>

    <!-- Tabs -->
    <div x-data="{ tab: 'MI' }" class="flex flex-col gap-6">
        <div class="flex border-b border-slate-200 dark:border-slate-800">
            <button @click="tab = 'MI'" :class="{ 'border-primary text-primary': tab === 'MI', 'border-transparent text-slate-500 hover:text-slate-700': tab !== 'MI' }" class="px-6 py-3 font-bold text-sm border-b-2 transition-colors">
                Tingkat MI
            </button>
            <button @click="tab = 'MTS'" :class="{ 'border-primary text-primary': tab === 'MTS', 'border-transparent text-slate-500 hover:text-slate-700': tab !== 'MTS' }" class="px-6 py-3 font-bold text-sm border-b-2 transition-colors">
                Tingkat MTs
            </button>
        </div>

        <!-- MI Form -->
        <div x-show="tab === 'MI'" class="animate-fade-in">
            @include('settings.partials.school-form', ['school' => $mi, 'jenjang' => 'MI'])
        </div>

        <!-- MTs Form -->
        <div x-show="tab === 'MTS'" style="display: none;" class="animate-fade-in">
             @include('settings.partials.school-form', ['school' => $mts, 'jenjang' => 'MTS'])
        </div>
    </div>
</div>

<!-- AlpineJS for simple tabs -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection

