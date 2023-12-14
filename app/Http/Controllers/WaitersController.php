<?php

namespace App\Http\Controllers;

use App\Models\Waiters;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class WaitersController extends Controller
{
    public function __construct()
    {
        if (!Session::get('waitersLogin')) {
            return redirect()->route('waitersSignIn')->with('alertWaiters', 'Anda Harus Login Terlebih Dahulu!');
        }
    }

    public function waitersSignIn(){
        return view('form.waitersSignIn');
    }

    public function waitersSignInPost(Request $request){

        if (!empty($request->email) && !empty($request->password) ) {
            $email = $request->email;
            $password = ($request->password);

            $data = Waiters::where('email',$email)->first();
            if($data){ //apakah email tersebut ada atau tidak
                if($password == $data->password){
                    Session::put('id', $data->id);
                    Session::put('nama',$data->nama);
                    Session::put('email',$data->email);
                    Session::put('level', 'waiters');
                    Session::put('waitersLogin',TRUE);

                    // ceritanya redirect ke landing dulu
                    return redirect()->route('showOrderWaiters');
                }
                else{
                    return redirect()->route('waitersSignIn')->with('alertWaiters','Password, Salah !');
                }
            }
            else{
                return redirect()->route('waitersSignIn')->with('alertWaiters','Email Belum Terdaftar!');
            }
        }else {
            if (empty($request->email) && empty($request->password)) {
                return redirect()->route('waitersSignIn')->with('alertWaiters','Email atau Password Tidak Boleh Kosong!');
            }
            elseif (empty($request->email)) {
                return redirect()->route('waitersSignIn')->with('alertWaiters','Email Tidak Boleh Kosong!');
            }
            elseif (empty($request->password)) {
                return redirect()->route('waitersSignIn')->with('alertWaiters','Password Tidak Boleh Kosong!');
            }
        }
    }

    public function waitersLogout(){
        Session::flush();
        return redirect()->route('waitersSignIn')->with('alertWaiters','Anda Telah Logout!');
    }

}
