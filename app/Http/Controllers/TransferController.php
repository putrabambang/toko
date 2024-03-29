<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Barang;

class TransferController extends Controller
{
    public function index()
    {
        return view('Transfer.index');
    }

    public function data()
    {
        $transfer = Transfer::orderBy('id_transfer', 'desc')->get();

        return datatables()
            ->of($transfer)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($transfer) {
                return tanggal_indonesia($transfer->created_at, false);
            })
            ->addColumn('total_item', function ($transfer) {
                return format_uang($transfer->total_item);
            })
            ->addColumn('total_harga', function ($transfer) {
                return 'Rp. ' . format_uang($transfer->total_harga);
            })
            
            ->editColumn('kasir', function ($transfer) {
                return $transfer->user->name ?? '';
            })
            ->addColumn('aksi', function ($transfer) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`' . route('transfer.show', $transfer->id_transfer) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`' . route('transfer.destroy', $transfer->id_transfer) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->addColumn('status', function ($transfer) {
                $status= $transfer->status ;
       if($status == 1){
           $cek = '<span onclick="konfirmasi(`'.route('transfer.konfirmasi',$transfer->id_transfer).'`)" class="label label-danger "><i class="">belum di setujui</i></span>';

       }else{

           $cek = '<span onclick="batalkonfir(`'.route('transfer.batalkonfir',$transfer->id_transfer).'`)" class="label label-success "><i class="">sudah di setujui</i></span>' ;
    
       }
                return $cek ;
            })
            ->rawColumns(['aksi', 'status'])
            ->make(true);
    }

    public function create()
    {
        $transfer = new Transfer();
        $transfer->total_item  = 0;
        $transfer->id_user = auth()->id();
        $transfer->role = 1;
        $transfer->status = 1;
        $transfer->save();

        session(['id_transfer' => $transfer->id_transfer]);

        return redirect()->route('transfer_detail.index');
    }

    public function store(Request $request)
    {
        $transfer = Transfer::findOrFail($request->id_transfer);
        $transfer->total_item = $request->total_item;
        $transfer->role = $request->role;
        $transfer->update();

       
    }

    public function show($id)
    {
        $detail = TransferDetail::with('Barang')->where('id_transfer', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_barang', function ($detail) {
                return '<span class="label label-success">' . $detail->barang->kode_barang . '</span>';
            })
            ->addColumn('nama_barang', function ($detail) {
                return $detail->barang->nama_barang;
            })

            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })

            ->rawColumns(['kode_barang'])
            ->make(true);
    }
    public function konfirmasi($id){
        $transfer = transfer::find($id);
    
        // Periksa apakah status transfer belum 2
        if ($transfer && $transfer->status != 2) {
            $transfer->status = 2;
            $transfer->update();
            
            $detail = TransferDetail::where('id_transfer', $transfer->id_transfer)->get();
    
            // Iterasi melalui setiap detail transfer
            foreach ($detail as $item) {
                // Temukan barang berdasarkan id_barang dari detail transfer
                $barang = Barang::find($item->id_barang);
    
                // Pastikan ada data barang dengan id_barang yang sesuai
                if ($barang) {
                    // Jika role adalah 1, maka kurangi stok_gudang dan tambahkan stok
                    if ($transfer->role == 1) {
                        $barang->stok_gudang -= $item->jumlah;
                        $barang->stok += $item->jumlah;
                    } else {
                        // Jika role adalah 0, maka tambahkan stok_gudang dan kurangi stok
                        $barang->stok_gudang += $item->jumlah;
                        $barang->stok -= $item->jumlah;
                    }
                    $barang->save(); // Simpan perubahan ke database
                }
            }
    
            return response()->json('Data berhasil disimpan', 200);
        } else {
            return response()->json('Status transfer sudah 2 atau data tidak ditemukan', 400);
        }
    }
    
    public function destroy($id)
    {
        $transfer = Transfer::find($id);
        $detail = TransferDetail::where('id_transfer', $transfer->id_transfer)->get();

        foreach ($detail as $item) {
            $barang = Barang::find($item->id_barang);
            $barang->stok_gudang += $item->jumlah; // Kembalikan stok_gudang yang telah dipindahkan
            $barang->stok -= $item->jumlah;   // Kurangi stok yang telah dipindahkan
            $barang->save();
            $item->delete();
        }

        $transfer->delete();

        return response(null, 204);
    }
}
