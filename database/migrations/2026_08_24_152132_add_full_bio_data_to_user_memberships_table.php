<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            // Personal details
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('gender')->nullable()->after('phone');
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('place_of_birth');
            $table->string('local_government_area')->nullable()->after('state_of_origin');
            $table->string('marital_status')->nullable()->after('local_government_area');

            // Contact & address
            $table->text('residential_address')->nullable()->after('marital_status');
            $table->text('postal_address')->nullable()->after('residential_address');
            $table->string('next_of_kin_name')->nullable()->after('postal_address');
            $table->text('next_of_kin_address')->nullable()->after('next_of_kin_name');

            // Educational qualifications
            $table->string('primary_school')->nullable()->after('next_of_kin_address');
            $table->string('primary_school_year')->nullable()->after('primary_school');
            $table->string('secondary_school')->nullable()->after('primary_school_year');
            $table->string('secondary_school_year')->nullable()->after('secondary_school');
            $table->string('higher_institution')->nullable()->after('secondary_school_year');
            $table->string('higher_institution_course')->nullable()->after('higher_institution');
            $table->string('higher_institution_year')->nullable()->after('higher_institution_course');
            $table->string('higher_institution_grade')->nullable()->after('higher_institution_year');
            $table->string('higher_institution_second_degree')->nullable()->after('higher_institution_grade');

            // Professional background
            $table->boolean('belongs_to_other_institute')->default(false)->after('higher_institution_second_degree');
            $table->string('other_institute_name')->nullable()->after('belongs_to_other_institute');
            $table->string('other_institute_status')->nullable()->after('other_institute_name');
            $table->string('other_institute_membership_number')->nullable()->after('other_institute_status');
            $table->string('year_of_qualification')->nullable()->after('other_institute_membership_number');

            // Declaration
            $table->string('declaration_name')->nullable()->after('year_of_qualification');
            $table->timestamp('declaration_accepted_at')->nullable()->after('declaration_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->dropColumn([
                'middle_name', 'gender', 'place_of_birth', 'nationality', 'local_government_area', 'marital_status',
                'residential_address', 'postal_address', 'next_of_kin_name', 'next_of_kin_address',
                'primary_school', 'primary_school_year', 'secondary_school', 'secondary_school_year',
                'higher_institution', 'higher_institution_course', 'higher_institution_year',
                'higher_institution_grade', 'higher_institution_second_degree',
                'belongs_to_other_institute', 'other_institute_name', 'other_institute_status',
                'other_institute_membership_number', 'year_of_qualification',
                'declaration_name', 'declaration_accepted_at',
            ]);
        });
    }
};
