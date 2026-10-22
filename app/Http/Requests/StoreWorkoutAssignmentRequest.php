<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\WorkoutAssignment;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkoutAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $tenantContext = app(TenantContext::class);
        $organization = $tenantContext->requireOrganization();

        return $user->hasPermission('fitness.manage', $organization, $tenantContext->branch())
            || Member::query()
                ->whereBelongsTo($organization)
                ->where('user_id', $user->id)
                ->whereKey($this->integer('member_id'))
                ->exists();
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)],
            'name' => ['required', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'exercises' => ['nullable', 'string', 'max:5000'],
            'weekly_schedule' => ['required', 'array:'.implode(',', array_keys(WorkoutAssignment::WEEKDAYS))],
            'weekly_schedule.*' => ['nullable', 'string', 'max:3000'],
            'instructions' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $schedule = $this->input('weekly_schedule', []);

            if (is_array($schedule) && collect($schedule)->filter(fn ($exercises): bool => is_string($exercises) && trim($exercises) !== '')->isEmpty()) {
                $validator->errors()->add('weekly_schedule', 'Add exercises for at least one day.');
            }
        }];
    }
}
