<?php

namespace App\Http\Controllers;

use App\Models\Foto;
use App\Models\User;
use App\Models\Like;
use App\Models\Komentar;
use App\Models\Album;
use App\Models\Report;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function adminds(Request $request)
    {
        $datausersdet = User::where('role','users')->orderby('id','DESC')->get();
        $datausers          = User::where('role' , 'users')->count();
        $dataalbum          = Album::all()->count();
        $dataupload         = Foto::all()->count();
        $datareport         = Report::all()->count();
        return view('admin.adminds', compact('datausersdet','datausers', 'dataalbum','dataupload','datareport'));
    }
    public function dataalbum()
    {

    $dataalbum = Album::with('user')->get();

    return view('admin.dataalbum', compact('dataalbum'));
    }
    public function datareport()
    {
        $dataupload = Report::with('user', 'foto')->get();

        return view('admin.datareport', compact('dataupload'));
    }

    public function hapususer(Request $request, $id) {
        $user = User::findOrFail($id);

        $user->Report()->delete();

        foreach ($user->Album as $album) {
            foreach ($album->foto as $foto) {
                $foto->Report()->delete();
                $foto->Komentar()->delete();
                $foto->Like()->delete();
                $foto->delete();
            }
            $album->delete();
        }

        $user->like()->delete();

        $user->Komentar()->delete();
        $user->foto()->delete();
        $user->delete();
        Alert::success('User Berhasil di Hapus');
        return redirect('/adminds');
    }


public function hapusalbum(Request $request, $id)
{
    try {
        // Dapatkan album berdasarkan ID
        $album = Album::findOrFail($id);

        // Hapus semua foto yang terkait dengan album
        foreach ($album->foto as $foto) {
            // Hapus semua like yang terkait dengan foto
            $foto->like()->delete();

            $foto->report()->delete();
            // Hapus semua komentar yang terkait dengan foto
            $foto->komentar()->delete();

            // Hapus foto itu sendiri
            $foto->delete();
        }

        // Hapus album
        $album->delete();

        // Redirect ke halaman admin setelah penghapusan berhasil
        return redirect('/dataalbum')->with('success', 'Album berhasil dihapus');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Tangani jika album tidak ditemukan
        return redirect('/dataalbum')->with('error', 'Album tidak ditemukan');
    } catch (\Exception $e) {
        // Tangani kesalahan umum
        return redirect('/dataalbum')->with('error', 'Terjadi kesalahan saat menghapus album');
    }

}


public function hapusreport(Request $request, $id, Foto $foto) {

        try {
            // Temukan data laporan
            $report = Report::find($id);

            // Periksa apakah data laporan ditemukan
            if (!$report) {
                return redirect('/datareport')->with('error', 'Data laporan tidak ditemukan.');
            }

            // Hapus data komen terkait
            Komentar::where('foto_id', $report->foto_id)->delete();

            // Hapus data like terkait
            Like::where('foto_id', $report->foto_id)->delete();

            // Hapus data laporan
            $report->delete();

            // Hapus data foto terlebih dahulu (jika ada)
            if ($report->foto) {
                // Hapus referensi dari tabel foto
                $report->foto->save();

                // Hapus data foto
                Foto::destroy($report->foto->id);
            }

            return redirect('/datareport')->with('success', 'Report berhasil dihapus beserta relasinya.');
        } catch (\Exception $e) {
            return redirect('/datareport ')->with('error', 'Gagal menghapus report dan relasinya: ' . $e->getMessage());
        }
    }

public function logoutdiadmin(Request $request)
{
                // proses penghancuran session
                $request->session()->invalidate();
                // proses pembuatan session baru
                $request->session()->regenerate();
                // menampilkan halaman utama setelah logout
                Alert::success('Berhasil Log out');
                return redirect('/');
}
}
