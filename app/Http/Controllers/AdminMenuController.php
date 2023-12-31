<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class AdminMenuController extends Controller
{
    public function __construct()
    {
        if (!Session::get('adminLogin')) {
            return redirect()->route('adminSignIn')->with('alert', 'Anda Harus Login Terlebih Dahulu!');
        }
    }

    // menampilkan tabel Makanan
    public function showMakananAdmin()
    {
        if (!Session::get('adminLogin')) {
            return redirect()->route('adminSignIn')->with('alert', 'Anda Harus Login!');
        }
        else {
            $foods = Menu::all()->where('jenis','makanan');
            // redirect ke view tabel makanan
            return view('admin.tabelMakanan', ['foods' => $foods]);
        }
    }

    // menampilkan tabel Minuman
    public function showMinumanAdmin()
    {
        if (!Session::get('adminLogin')) {
            return redirect()->route('adminSignIn')->with('alert', 'Anda Harus Login!');
        }
        else {
            $drinks = Menu::all()->where('jenis','minuman');
            // redirect ke view tabel minuman
            return view('admin.tabelMinuman', ['drinks' => $drinks]);
        }
    }

    public function searchMenu(Request $request, $kategori)
    {
        $result = Menu::select('*')
                ->where('nama', 'like', "%{$request->search}%")
                ->where('jenis', $kategori)
                ->orwhere('harga', 'like', "%{$request->search}%")
                ->orWhere('stok', 'like', "%{$request->search}%")
                ->get();
        
        if ($kategori == 'makanan') {
            return view('admin.tabelMakanan', ['foods' => $result]);
        }
        else {
            return view('admin.tabelMinuman', ['drinks' => $result]);
        }
    }

    // insert data menu ke database
    public function addMenu(Request $request, $kategori)
    {
        // ambil dan validasi data dari form
        $validateData = $request->validate([
            'nama'          => 'required|regex:/^[\pL\s\-]+$/u|unique:menus',
            'harga'         => 'required|numeric',
            'stok'          => 'required|numeric',
            'foto'          => 'required|image|mimes:jpeg,png,jpg|max:2048',

        ]);

        // ambil file foto
        $foto = $validateData['foto'];
        $nama_foto = $foto->getClientOriginalName();
        $directory = 'storage/img';
        $foto->move($directory, $nama_foto);

        $data = new Menu();
        $data->nama = $validateData['nama'];
        $data->jenis = $kategori;
        $data->harga = $validateData['harga'];
        $data->stok = $validateData['stok'];
        $data->foto = $nama_foto;
        $data->save();
        
        if ($kategori == 'makanan') {
            return redirect()->route('showMakananAdmin')->with('pesan',"Penambahan data berhasil");
        }
        else {
            return redirect()->route('showMinumanAdmin')->with('pesan',"Penambahan data berhasil");
        }
    }

    // update data menu
    public function editMenu(Request $request, $kategori)
    {
        $data_update = Menu::find($request->id);
        // ambil dan validasi data dari form
        $validateData = $request->validate([
            'nama'          => 'required|regex:/^[\pL\s\-]+$/u',
            'harga'         => 'required|numeric',
            'stok'          => 'required|numeric',
            'foto'          => 'image|mimes:jpeg,png,jpg|max:2048',

        ]);

        if ($request->foto != "") {
            File::delete('storage/img/'.$data_update->foto);

            // ambil file foto
            $foto = $validateData['foto'];
            $nama_foto = $foto->getClientOriginalName();
            $directory = 'storage/img';
            $foto->move($directory, $nama_foto);

            $data_update->foto = $nama_foto;
        }
        
        $data_update->nama = $validateData['nama'];
        $data_update->harga = $validateData['harga'];
        $data_update->stok = $validateData['stok'];
        $data_update->save();

        if ($kategori == 'makanan') {
            return redirect()->route('showMakananAdmin')->with('pesan',"Update data Menu {$request->namaMenu} berhasil");
        }else{
            return redirect()->route('showMinumanAdmin')->with('pesan',"Update data Menu {$request->namaMenu} berhasil");
        }
    }

    // hapus data menu dari db
    public function deleteMenu($id)
    {
        $data = Menu::find($id);
        $nama_menu = $data->nama;
        $kategori = $data->jenis;
        File::delete('storage/img/'.$data->foto);       //hapus foto
        Menu::destroy($id);

        if ($kategori == 'makanan') {
            return redirect()->route('showMakananAdmin')->with('pesan',"Menghapus Menu {$nama_menu} berhasil");
        }
        else {
            return redirect()->route('showMinumanAdmin')->with('pesan',"Menghapus Menu {$nama_menu} berhasil");
        }
    }

}
