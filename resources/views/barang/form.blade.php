<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                <div class="form-group row">
                        <label for="kode_barang" class="col-lg-2 col-lg-offset-1 control-label">kode barang</label>
                        <div class="col-lg-6">
                            <input type="text" name="kode_barang" id="kode_barang" class="form-control" >
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="nama_barang" class="col-lg-2 col-lg-offset-1 control-label">Nama Barang</label>
                        <div class="col-lg-6">
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="kategori" class="col-lg-2 col-lg-offset-1 control-label">Kategori</label>
                        <div class="col-lg-6">
                            <select name="id_kategori" id="id_kategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategori as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                     <div class="form-group row">
                        <label for="harga_jual" class="col-lg-2 col-lg-offset-1 control-label">Harga Jual</label>
                        <div class="col-lg-6">
                        <input type="numbertext" name="harga_jual" id="harga_jual" class="form-control" value="0">
                        <span class="help-block with-errors"></span>
                        </div>
                    </div>  
                    <div class="form-group row">
                        <label for="modal" class="col-lg-2 col-lg-offset-1 control-label">Modal</label>
                        <div class="col-lg-6">
                        <input type="numbertext" name="modal" id="modal" class="form-control" value="0">
                        <span class="help-block with-errors"></span>
                        </div>
                    </div>  
                    <div class="form-group row">
                        <label for="stok" class="col-lg-2 col-lg-offset-1 control-label">Stok Toko</label>
                        <div class="col-lg-6">
                            <input type="number" name="stok" id="stok" class="form-control" required value="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="stok_gudang" class="col-lg-2 col-lg-offset-1 control-label">Stok Gudang</label>
                        <div class="col-lg-6">
                            <input type="number" name="stok_gudang" id="stok_gudang" class="form-control" required value="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tambahstok" class="col-lg-2 col-lg-offset-1 control-label">Tambah Stok</label>
                        <div class="col-lg-6">
                            <input type="number" name="tambahstok" id="tambahstok" class="form-control" required value="0"autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>