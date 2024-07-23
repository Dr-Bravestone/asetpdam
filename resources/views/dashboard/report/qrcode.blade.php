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
                            <button type="button" class="btn tombol print" onclick="previewPrint('{{ asset('storage/' . $asset->gambar_qr) }}', {{ $asset->jumlah }})">Preview Print</button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="previewModalLabel">Preview Print QR Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="previewModalBody">
                    <!-- Content will be dynamically added here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printPreview()">Print</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        #previewModalBody img {
            margin-bottom: 20px; /* Adjust as needed */
            margin-right: 20px; /* Adjust as needed */
        }
    </style>

    <script>
        function previewPrint(imageUrl, jumlah) {
            var previewContent = '';
            for (var i = 0; i < jumlah; i++) {
                previewContent += '<img src="' + imageUrl + '" style="max-width: 100%; margin-bottom: 10px; margin-right: 10px;">';
            }
            $('#previewModalBody').html(previewContent);
            $('#previewModal').modal('show');
        }

        function printPreview() {
            var printWindow = window.open('', 'Print Preview');
            var previewContent = $('#previewModalBody').html();
            var styles = '<style>\
                            body { text-align: left; }\
                            #previewModalBody img { margin-bottom: 10px; margin-right: 10px; }\
                          </style>';
            printWindow.document.open();
            printWindow.document.write('<html><head><title>Print Preview</title>' + styles + '</head><body>');
            printWindow.document.write(previewContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endsection
