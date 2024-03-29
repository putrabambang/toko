<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use Alert;
use PDF;

class barangcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategori = kategori::all()->pluck('nama_kategori','id_kategori');
        return view ('barang.index',compact('kategori'));
    }
public function data()
{
   $barang = barang::leftJoin('kategori','kategori.id_kategori','barang.id_kategori')
   ->select('barang.*','nama_kategori')
   //->orderBy('kode_barang','asc') 
   ->get();

   return datatables()
       ->of ($barang)
       ->addColumn('select_all',function($barang){
        return '
        <input type="checkbox" name="id_barang[]" value="'. $barang->id_barang .'">
        ';
    })
       ->addColumn('kode_barang',function ($barang){
        return '<span class="label label-success">'.$barang->kode_barang.'</span>';
    })
       ->addColumn('harga_jual',function ($barang){
           return  'Rp. '.format_uang ($barang->harga_jual);
       })
       ->addColumn('modal',function ($barang){
        return'Rp. '.format_uang($barang->modal);
    })
       ->addColumn('subtotal',function ($barang){
        return (($barang->stok + $barang->stok_gudang) * $barang->harga_jual);
    })
       ->addindexColumn()
       ->addColumn('aksi',function($barang){
           return'
           <div class="btn-group">
           <button type="button"onclick="editForm(`'.route('barang.update',$barang->id_barang).'`)"  class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
           <button type="button"onclick="deleteData(`'.route('barang.destroy',$barang->id_barang).'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
           <button type="button" onclick="tambahStok(`'.$barang->id_barang.'`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-plus"></i></button>
           
           </div>
           ';
       })
       ->rawColumns(['aksi','kode_barang', 'select_all'])
       ->make(true);

}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


     public function tambahStok(Request $request)
{
    $id_barang = $request->input('id_barang');
    $jumlah_stok = $request->input('jumlah_stok');

    try {
        $barang = Barang::find($id_barang);
        $barang->stok += $jumlah_stok;
        $barang->save();

        return response()->json(['message' => 'Stok barang berhasil ditambah'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Terjadi kesalahan saat menambah stok barang'], 500);
    }
}
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $setting = Setting::first();
        $kodebarang = '';
    
        $existingBarang = Barang::pluck('kode_barang')->toArray();
        $maxKodeBarang = max($existingBarang);
        $maxNourut = (int)substr($maxKodeBarang, -5);
    
        for ($i = 1; $i <= $maxNourut; $i++) {
            $currentKodeBarang = $setting->kode_barang . tambah_nol_didepan($i, 5);
            if (!in_array($currentKodeBarang, $existingBarang)) {
                $kodebarang = $currentKodeBarang;
                break;
            }
        }
    
        // Jika tidak ada kode barang yang terlewatkan, gunakan kode barang berikutnya
        if (empty($kodebarang)) {
            $nextNourut = $maxNourut + 1;
            $kodebarang = $setting->kode_barang . tambah_nol_didepan($nextNourut, 5);
        }
    
        $barang = new Barang();
        $barang->kode_barang = $kodebarang;
        $barang->nama_barang = $request->nama_barang;
        $barang->id_kategori = $request->id_kategori;
        $barang->harga_jual = $request->harga_jual;
        $barang->modal = $request->modal;
        $barang->stok = $request->stok;
        $barang->stok_gudang = $request->stok_gudang;
        $barang->save();
    
        return response()->json('Data berhasil disimpan', 200);
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     
     
    public function show($id)
    {
    $barang = barang::find($id);

    return response()->json($barang);
    }


/////
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $barang = barang::find($id);
        $barang->kode_barang = $request->kode_barang;
        $barang->nama_barang = $request->nama_barang;
        $barang->id_kategori = $request->id_kategori;
        $barang->harga_jual = $request->harga_jual;
        $barang->stok = $request->stok;
        $barang->modal= $request->modal;
        $barang->stok_gudang = $request->stok_gudang;
        $barang->update();


        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $barang = barang::find($id);
        $barang->delete();

        return response(null, 204);
    }
    public function deleteSelected(Request $request)
    {
        foreach ($request->id_barang as $id) {
            $barang = barang::find($id);
            $barang->delete();
        }

        return response(null, 204);
    }
    public function cetakBarcode(Request $request)
    {
        $jumlahcetak=$request->jumlahcetak;
        $databarang = array();
        foreach ($request->id_barang as $id) {
            $barang = barang::find($id);
            $databarang[] = $barang;
        }

        $no  = 1;
        return view ('barang.barcode', compact('databarang', 'no','jumlahcetak'));
    }
}


