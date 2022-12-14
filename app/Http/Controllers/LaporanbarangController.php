<?php

namespace App\Http\Controllers;
use App\Models\penjualandetail;
use App\Models\barang;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PDF;

class LaporanbarangController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-01');
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporanbarang.index', compact('tanggalAwal', 'tanggalAkhir'));
    }
    public function data($awal, $akhir)
    {   $tanggal = $awal;
        $tanggalAkhir =$akhir;
  
        $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));
        $barang= PenjualanDetail::with('barang')
        ->select('id_barang',
        DB::raw('SUM(jumlah) as jumlah_penjualan'))
       ->wherebetween('created_at',["$tanggal","$tanggalAkhir"])
       ->orwhere('created_at','like',"%$tanggalAkhir%")
        ->ORDERBY ('jumlah_penjualan', 'desc')
        ->GROUPBY('id_barang')
        ->get();
            return datatables()
            ->of($barang)
            ->addIndexColumn()
            ->addColumn('kode_barang', function ($barang) {
                return '<span class="label label-success">'. $barang->barang->kode_barang .'</span>';
            })
            ->addColumn('nama_barang', function ($barang) {
                return $barang->barang->nama_barang;
            })
            ->addColumn('harga_jual', function ($barang) {
                return 'Rp. '. format_uang($barang->barang->harga_jual);
            })
            ->addColumn('jumlah', function ($barang) {
                return format_uang($barang->jumlah_penjualan);
            })
            ->addColumn('subtotal', function ($barang) {
                return ($barang->jumlah_penjualan * $barang->barang->harga_jual);
            })
            ->rawColumns(['kode_barang'])
            ->make(true);
}

    public function exportPDF($awal, $akhir)
    {
        $data = $this->Data($awal, $akhir);
        $pdf  = PDF::loadView('laporanbarang.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'potrait');
        
        return $pdf->stream('Laporan-barang-'. date('Y-m-d-his') .'.pdf');
    }
}
