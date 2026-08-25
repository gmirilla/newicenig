<?php

namespace App\Services\LegacyMigration;

/**
 * Maps the old app's EAV `h_t_m_l_form_fields.name` keys (reverse-engineered
 * from the live old membership form) onto the new `UserMembership` schema's
 * typed columns. Anything not in this table is preserved, not dropped — it
 * lands in `extra_fields` so nothing submitted by a real applicant is lost.
 */
class FieldMapper
{
    /**
     * Old EAV field name => new `UserMembership` column name.
     *
     * @var array<string, string>
     */
    protected const COLUMN_MAP = [
        'first_name' => 'first_name',
        'middle_name' => 'middle_name',
        'last_name' => 'last_name',
        'gender' => 'gender',
        'date_of_birth' => 'date_of_birth',
        'place_of_birth' => 'place_of_birth',
        'nationality' => 'nationality',
        'marital_status' => 'marital_status',
        'state_of_origin' => 'state_of_origin',
        'local_government_area' => 'local_government_area',
        'residential_address' => 'residential_address',
        'current_postal_address' => 'postal_address',
        'next_of_kin_name' => 'next_of_kin_name',
        'next_of_kin_address' => 'next_of_kin_address',
        'primary_school' => 'primary_school',
        'primary_school_year_passed_out' => 'primary_school_year',
        'secondary_school' => 'secondary_school',
        'secondary_school_year_passed_out' => 'secondary_school_year',
        'higher_instition' => 'higher_institution',
        'higher_instition_course_offer' => 'higher_institution_course',
        'higher_instition_year_passed_out' => 'higher_institution_year',
        'higher_instition_final_grade' => 'higher_institution_grade',
        'higher_instition_second_degree' => 'higher_institution_second_degree',
        'do_you_belong_to_any_professional_institute_like_ours' => 'belongs_to_other_institute',
        'if_yes_name_of_the_institute' => 'other_institute_name',
        'your_status_in_the_institute' => 'other_institute_status',
        'quote_your_membership_number_of_the_institue' => 'other_institute_membership_number',
        'year_of_qualification' => 'year_of_qualification',
        'declaration_signature' => 'declaration_name',
    ];

    /**
     * Old field names for uploaded documents — these are reconstructed from
     * the `files` table by {@see FileImporter}, not from EAV values, so they're
     * excluded from both the column map and the `extra_fields` safety net.
     *
     * @var array<int, string>
     */
    protected const FILE_FIELDS = [
        'passport',
        'primary_school_certificate',
        'secondary_school_certificate',
        'higher_instition_certificate',
    ];

    protected const BOOLEAN_FIELDS = [
        'belongs_to_other_institute',
    ];

    /**
     * @param  array<string, string>  $formData  Old field name => submitted value.
     * @return array{columns: array<string, mixed>, extra: array<string, mixed>}
     */
    public function map(array $formData): array
    {
        $columns = [];
        $extra = [];

        foreach ($formData as $oldName => $value) {
            if (in_array($oldName, self::FILE_FIELDS, true)) {
                continue;
            }

            $column = self::COLUMN_MAP[$oldName] ?? null;

            if ($column === null) {
                $extra[$oldName] = $value;

                continue;
            }

            $columns[$column] = in_array($column, self::BOOLEAN_FIELDS, true)
                ? $this->toBoolean($value)
                : ($value === '' ? null : $value);
        }

        return ['columns' => $columns, 'extra' => $extra];
    }

    protected function toBoolean(?string $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return strtolower($value) === 'yes';
    }
}
