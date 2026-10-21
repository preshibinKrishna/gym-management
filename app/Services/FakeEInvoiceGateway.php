<?php

namespace App\Services;

use App\Contracts\EInvoiceGateway;
use App\Models\Invoice;

class FakeEInvoiceGateway implements EInvoiceGateway
{
    public function register(Invoice $invoice): array
    {
        return ['provider' => 'fake', 'status' => 'simulated', 'irn' => 'SIMULATED-'.$invoice->number, 'message' => 'No statutory IRP submission was made.'];
    }
}
