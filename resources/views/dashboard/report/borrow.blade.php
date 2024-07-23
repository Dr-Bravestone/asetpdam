@extends('dashboard.report.main')

@section('container')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Data Peminjaman</h1>  
        <div class="mt-2"><h6>Login sebagai: {{ auth()->user()->email }}</h6></div>

    </div>

    <div class="card-body">
        <div class="table-responsive col-lg-12 mb-3">
            <table id="myTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">P. Jawab</th>
                        <th scope="col">Nama Aset</th>
                        {{-- <th scope="col">Ruangan</th> --}}
                        <th scope="col">Tanggal</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pinjamaset as $pinjam)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pinjam->pj }}</td>
                            <td>{{ $pinjam->asset->nama }}</td>
                            {{-- <td>{{ $pinjam->room->nama }}</td> --}}
                            <td>{{ $pinjam->tgl_pinjam }}</td>
                            <td>{{ $pinjam->durasi }} hari</td>
                            <td>
                                @if ($pinjam->status == 'Dipinjam')
                                    <div class="badge bg-warning">Dipinjam</div>
                                @else
                                    <div class="badge bg-success">{{ $pinjam->status }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
