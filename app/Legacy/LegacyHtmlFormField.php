<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view of the old app's `h_t_m_l_form_fields` table — the EAV field
 * definitions (e.g. `first_name`, `higher_instition`) used by the dynamic
 * membership application form.
 */
class LegacyHtmlFormField extends Model
{
    protected $connection = 'legacy';

    protected $table = 'h_t_m_l_form_fields';
}
