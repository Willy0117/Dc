<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:certificates.view'),
        ];
    }

    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'issued_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = (int) $request->input('per_page', 20);

        $allowedSorts = ['id', 'issued_at', 'certificate_number'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'issued_at';
        }

        $certificates = Certificate::with(['order.videoSet', 'video'])
            ->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => [
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function download(Certificate $certificate)
    {
        return Storage::disk('local')->download(
            $certificate->pdf_path,
            "{$certificate->certificate_number}.pdf"
        );
    }
}
