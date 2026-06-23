<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ExportController extends Controller
{
    public function exports(Request $request)
    {
        if ($request->ajax()) {
            $exports = ExportJob::where('user_id', Auth::id())
                ->where('source', 'guru')
                ->latest()
                ->select('export_jobs.*');

            return DataTables::of($exports)
                ->addIndexColumn()
                ->addColumn('type_label', fn($row) => $row->type_label)
                ->addColumn('status_badge', function ($row) {
                    return match ($row->status) {
                        'completed' => '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Selesai</span>',
                        'processing' => '<span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Memproses</span>',
                        'failed' => '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full" title="' . e($row->error_message) . '">Gagal</span>',
                        default => '<span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">Menunggu</span>',
                    };
                })
                ->addColumn('aksi', function ($row) {
                    $btn = '';
                    if ($row->status === 'completed') {
                        $btn .= '<a href="' . route('guru.export-download', $row) . '" class="text-green-600 hover:text-green-800 mr-3" title="Download">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>';
                    } elseif ($row->status === 'failed') {
                        $btn .= '<span class="text-red-500 cursor-help mr-3" title="' . e($row->error_message) . '">Gagal</span>';
                    } else {
                        $btn .= '<span class="text-gray-400 mr-3">Proses...</span>';
                    }
                    $btn .= '<form method="POST" action="' . route('guru.export-destroy', $row) . '" class="inline" onsubmit="return confirm(\'Hapus export ini?\')">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>';
                    return $btn;
                })
                ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y H:i'))
                ->rawColumns(['status_badge', 'aksi'])
                ->make(true);
        }

        return view('admin.laporan.exports', ['isAdmin' => false]);
    }

    public function downloadExport(ExportJob $exportJob)
    {
        abort_if($exportJob->user_id !== Auth::id(), 403);
        abort_if($exportJob->status !== 'completed', 404);

        return Storage::disk('local')->download($exportJob->filepath, $exportJob->filename);
    }

    public function destroyExport(ExportJob $exportJob)
    {
        abort_if($exportJob->user_id !== Auth::id(), 403);

        if ($exportJob->filepath && Storage::disk('local')->exists($exportJob->filepath)) {
            Storage::disk('local')->delete($exportJob->filepath);
        }

        $exportJob->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Export berhasil dihapus.']);
        }

        return redirect()->route('guru.exports')
            ->with('success', 'Export berhasil dihapus.');
    }
}
