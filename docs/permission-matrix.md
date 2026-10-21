# Permission Matrix

Phase 1 implements system roles with explicit permissions stored in `roles`, `permissions`, and `permission_role`.

| Role | Scope | Implemented permissions |
| --- | --- | --- |
| Platform super admin | Platform | All permissions through controlled platform flag; seeded for onboarding and health administration. |
| Organization owner | Organization | Organization, branch, role assignment, settings, member, billing, reports, exports, sensitive, CCTV permissions except platform management. |
| Organization admin | Organization | View/update organization operations, manage branches/settings, assign scoped roles, manage members/leads/plans/memberships/batches/attendance, collect payments, view/export reports. |
| Branch admin/manager | Branch | View/update assigned branch, manage members/leads/memberships/batches/attendance, collect payments, view reports. |
| Receptionist | Branch | Register members/leads, view plans, renew memberships, check in members, collect permitted payments. |
| Accountant | Organization | View organization/branches, collect/allocate payments, reports, exports. |
| Trainer | Branch | View assigned branch, batches, and permitted member records. |
| Dietitian | Branch | Pending Phase 4. |
| Maintenance staff | Branch | View assigned branch; maintenance module pending Phase 5. |
| Member | Member | Own member portal records when an account is linked. |
| Parent/guardian | Linked member | Explicit linked-child profile access and portal records when affiliated. |

Branch-scoped users must also have an active `branch_assignments` row for the selected branch. Menu visibility is not used as authorization.
