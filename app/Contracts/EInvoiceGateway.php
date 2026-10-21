<?php

namespace App\Contracts;

use App\Models\Invoice;

interface EInvoiceGateway
{
    /** @return array<string, mixed> */
    public function register(Invoice $invoice): array;
}
