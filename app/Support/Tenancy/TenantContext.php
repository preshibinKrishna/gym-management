<?php

namespace App\Support\Tenancy;

use App\Models\Branch;
use App\Models\Organization;

class TenantContext
{
    public function __construct(
        private ?Organization $organization = null,
        private ?Branch $branch = null,
    ) {}

    public function set(?Organization $organization, ?Branch $branch = null): void
    {
        $this->organization = $organization;
        $this->branch = $branch;
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function organizationId(): ?int
    {
        return $this->organization?->id;
    }

    public function branchId(): ?int
    {
        return $this->branch?->id;
    }

    public function requireOrganization(): Organization
    {
        abort_if($this->organization === null, 403, 'Tenant context is required.');

        return $this->organization;
    }

    public function requireBranch(): Branch
    {
        abort_if($this->branch === null, 403, 'Branch context is required.');

        return $this->branch;
    }
}
