<table class="w-full text-xs text-left border-collapse rapor-table">
    <thead class="bg-gray-100 text-center font-bold">
        <tr>
            <th class="w-8">No</th>
            <th>Kegiatan Ekstrakurikuler</th>
            <th class="w-10">Nilai</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $flatEkskuls = collect();
            if(isset($ekskuls)) {
                foreach($ekskuls as $pId => $grp) {
                    foreach($grp as $e) {
                        $ekey = $e->nama_ekskul; 
                        if (!$flatEkskuls->has($ekey)) {
                            $flatEkskuls->put($ekey, $e);
                        }
                    }
                }
            }
        @endphp
        
        @forelse($flatEkskuls as $e)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $e->nama_ekskul }}</td>
            <td class="text-center">{{ $e->nilai }}</td>
            <td>{{ $e->keterangan }}</td>
        </tr>
        @empty
        <tr>
            <td class="text-center">-</td>
            <td>-</td>
            <td class="text-center">-</td>
            <td>-</td>
        </tr>
        @endforelse
    </tbody>
</table>

