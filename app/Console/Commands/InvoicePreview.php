<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage; 
use App\Models\Organization;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use App\Services\PdfService;

class InvoicePreview extends Command
{
    protected $signature   = 'invoice:preview {id}';
    protected $description = 'Preview invoice PDF for organization';

    public function handle(PdfService $pdfService): void
    {
        $organization = Organization::with(['contract'])->findOrFail($this->argument('id'));

        $path = $pdfService->createInvoicePdf(
            $organization,
            [],
            'INV-TEST-00001',
            [
                ['ライセンス料（法人）', 1, 100000],
                ['ライセンス料（個人）', 2, 20000],
            ],
            120000,
            12000,
            132000,
            now()->toDateString(),
            now()->addDays(30)->toDateString(),
        );

        $this->info('PDF generated: ' . Storage::disk('public')->path($path));
    }
}