@extends('dashboard.report.main1')

@section('container')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Label QR Code Aset</h1>
    </div>
    <div class="container">
        <div class="row">
            @foreach ($assets as $asset)
                @if ($asset->gambar_qr)
                    <div class="col-lg-3 col-md-4 col-sm-5 col-xs-2 mb-3">
                        <div class="card" style="width: 9rem;">
                            <div class="cut">
                                <img src="{{ asset('storage/' . $asset->gambar_qr) }}" alt="{{ $asset->nama }}"
                                    class="img-fluid">
                            </div>
                            <small class="text-muted">
                                <b>Nama Aset : {{ $asset->nama }}</b><br>
                                Kode : {{ $asset->slug }}<br>
                                Ruangan : {{ $asset->room->nama }}<br>
                            </small>
                            <a href="{{ route('printqr.download', $asset->id) }}" class="btn tombol print">Download QR Code</a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
