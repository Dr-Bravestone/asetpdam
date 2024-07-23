<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Room;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Facades\Storage; 
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;

class AssetController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request)
    public function index(Request $request)
    {
        return view('dashboard.data-assets.index', [
            'title' => 'Data Aset',
            'assets' => Asset::all(),
            'categories' => Category::all(),
            'rooms' => Room::all(),
            // 'years' => $tahun
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kodeAset = "AST-".time();

        return view('dashboard.data-assets.create', [
            'title' => 'Tambah Aset',
            'slug' => $kodeAset,
            'categories' => Category::all(),
            'rooms' => Room::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'category_id' => 'required',
        'room_id' => 'required',
        'nama' => 'required|max:255',
        'slug' => 'required|unique:assets',
        'merek' => 'required',
        'jumlah' => 'required|numeric',
        'tahun' => 'required|numeric',
        'garansi' => 'required',
        'harga' => 'required',
        'make_qr' => 'required',
        'foto' => 'image|file|max:1024'
    ]);

    // Load the room from the database to access its name
    $room = Room::find($request->room_id);
    if (!$room) {
        return redirect()->back()->withErrors(['room_id' => 'Ruangan tidak ditemukan.']);
    }

    // Generate QR Code
    if ($request->make_qr == 1) {
        // Concatenate the required text fields into a single string with newline characters
        $qrContent = $request->slug . "\n" . $request->nama . "\n" . $room->nama;
    
        // Generate the QR code with the concatenated text
        $image = QrCode::format('png')->size(200)->generate($qrContent);
    
        // Define the output file path
        $output_file = 'qr-code/img-' . time() . '.png';
    
        // Save the generated QR code image to the specified path
        Storage::disk('public')->put($output_file, $image);
    
        // Store the file path in the validated data array
        $validatedData['gambar_qr'] = $output_file;
    }
    
    // Cek jika gambar ada isi
    if ($request->file('foto')) {
        $validatedData['foto'] = $request->file('foto')->store('assets-images', 'public');
    }

    // Insert data ke db
    Asset::create($validatedData);

    return redirect('/assets')->with('success', 'Asset berhasil ditambahkan!');
}


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function show(Asset $asset)
    {
        return view('dashboard.data-assets.show', [
            'title' => 'Detail Asset',
            'asset' => $asset
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function edit(Asset $asset)
    {
        return view('dashboard.data-assets.edit', [
            'title' => 'Edit Asset',
            'asset' => $asset,
            'categories' => Category::all(),
            'rooms' => Room::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Asset $asset)
{
    $validatedData = $request->validate([
        'category_id' => 'required',
        'room_id' => 'required',
        'nama' => 'required|max:255',
        'merek' => 'required',
        'jumlah' => 'required|numeric',
        'tahun' => 'required|numeric',
        'garansi' => 'required',
        'harga' => 'required',
        'make_qr' => 'required',
        'foto' => 'image|file|max:1024'
    ]);

    // Cek jika gambar sudah diupload
    if ($request->file('foto')) {
        // Jika gambar masih yang lama
        if ($request->oldImage) {
            // Maka hapus gambar lama tersebut dari folder penyimpanan
            Storage::delete($request->oldImage);
        }
        // Simpan gambar yang baru
        $validatedData['foto'] = $request->file('foto')->store('assets-images', 'public');
    }

    // Cek jika QR pilih no
    if ($request->make_qr == 0) {
        // Hapus QR dari penyimpanan jika ada
        if ($asset->gambar_qr) {
            Storage::disk('public')->delete($asset->gambar_qr);
        }
        $validatedData['gambar_qr'] = "";
    } else {
        // Muat room dari database untuk mendapatkan nama room
        $room = Room::find($request->room_id);
        if (!$room) {
            return redirect()->back()->withErrors(['room_id' => 'Ruangan tidak ditemukan.']);
        }

        // Concatenate the required text fields into a single string with newline characters
        $qrContent = $request->slug . "\n" . $request->nama . "\n" . $room->nama;

        // Generate the QR code with the concatenated text
        $image = QrCode::format('png')->size(200)->generate($qrContent);

        // Define the output file path
        $output_file = 'qr-code/img-' . time() . '.png';

        // Save the generated QR code image to the specified path
        Storage::disk('public')->put($output_file, $image);

        // Hapus QR code lama dari penyimpanan jika ada
        if ($asset->gambar_qr) {
            Storage::disk('public')->delete($asset->gambar_qr);
        }

        // Store the file path in the validated data array
        $validatedData['gambar_qr'] = $output_file;
    }

    // Insert data ke db
    Asset::where('id', $asset->id)->update($validatedData);

    return redirect('/assets')->with('success', 'Asset berhasil diedit!');
}


    public function printSelected($id)
    {
        // Logic untuk mengambil detail aset berdasarkan $id
        $asset = Asset::findOrFail($id);

        // Generate HTML view untuk mencetak QR Code aset yang dipilih
        $html = view('dashboard.report.print', [
            'asset' => $asset
        ])->render();

        // Setup opsi Dompdf (jika diperlukan)
        $options = new Options();
        $options->set('isPhpEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);


        // Instantiate Dompdf dengan opsi
        $dompdf = new Dompdf($options);

        // Load konten HTML
        $dompdf->loadHtml($html);

        // (Optional) Set ukuran kertas dan orientasi
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF (penting untuk dokumen besar)
        $dompdf->render();

        // Output PDF yang dihasilkan ke browser
        return $dompdf->stream();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function destroy(Asset $asset)
    {
        // jika ada gambar
        if ($asset->foto) {
            // maka hapus gambar dari penyimpanan
            Storage::delete($asset->foto);
        }
        // jika ada QR Code
        if ($asset->gambar_qr) {
            // maka hapus QR Code dari penyimpanan
            Storage::delete($asset->gambar_qr);
        }

        // cek jika asset sudah digunakan pada table borrow asset
        if ($asset->borrowassets()->count() ) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus aset! Aset memiliki data peminjaman.']);
        }

        // cek jika asset sudah digunakan pada table maintenance
        if ($asset->maintenances()->count() ) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus aset! Aset memiliki data maintenance.']);
        }

        // cek jika asset sudah digunakan pada table aset rusak
        if ($asset->damagedassets()->count() ) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus aset! Aset memiliki data aset rusak.']);
        }

        Asset::destroy($asset->id);
        return redirect('/assets')->with('success', 'Asset berhasil dihapus!');
    }

    public function checkSlug(Request $request)
    {
        $slug = SlugService::createSlug(Asset::class, 'slug', $request->nama);
        return response()->json(['slug' => $slug]);
    }

    public function reportaset()
    {
        return view('dashboard.report.aset', [
            'title' => 'Laporan Data Aset',
            'assets' => Asset::all()
        ]);
    }

    public function reportqr()
    {
        return view('dashboard.report.qrcode', [
            'title' => 'Label QR Code',
            'assets' => Asset::all()
        ]);
    }
    public function printqr()
    {
        $html = view('dashboard.report.printqr', [
            'assets' => Asset::all()
        ]);

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $options = $dompdf->getOptions();

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'potrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

    public function downloadQRCode($id)
{
    // Cari detail aset berdasarkan $id
    $asset = Asset::findOrFail($id);

    // Ambil path dari gambar QR Code
    $filePath = storage_path('app/public/' . $asset->gambar_qr);

    // Jika file tidak ada, kembalikan response not found
    if (!Storage::disk('public')->exists($asset->gambar_qr)) {
        abort(404);
    }

    // Atur header untuk response download
    $headers = [
        'Content-Type' => 'image/png', // Sesuaikan tipe konten jika perlu
    ];

    // Download file
    return response()->download($filePath, $asset->nama . '_qrcode.png', $headers);
}
}
