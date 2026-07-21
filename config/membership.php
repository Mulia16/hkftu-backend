<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Member Discount Percentage
    |--------------------------------------------------------------------------
    |
    | TODO(client): Confirm the official member discount percentage(s).
    |
    | Spec §10 describes a PricingRule engine keyed by member_type, course_type,
    | centre and season, but the ACTUAL discount figures are NOT specified
    | anywhere in the client documentation (tracked in §53 "Open Questions").
    | The values below are PLACEHOLDERS and are configurable via env until the
    | client confirms the real numbers. Do NOT treat these as business-approved.
    |
    | 'default'  applies to any active member whose member_type has no explicit
    |            override. 'by_type' lets specific member types override it
    |            (the §10 member_type dimension). Values are percentages (0-100).
    |
    */

    'member_discount_percentage' => [
        'default' => (float) env('MEMBER_DISCOUNT_PERCENTAGE', 10),

        'by_type' => [],
    ],

];
