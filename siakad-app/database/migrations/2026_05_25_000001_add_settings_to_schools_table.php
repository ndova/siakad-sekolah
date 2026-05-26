<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Tampilan login
            $table->string('portal_title', 200)->nullable()->after('logo');
            $table->string('welcome_text', 255)->nullable()->after('portal_title');
            $table->text('tagline')->nullable()->after('welcome_text');
            $table->string('landing_image', 255)->nullable()->after('tagline');
            $table->string('footer_text', 255)->nullable()->after('landing_image');
            $table->string('primary_color', 20)->default('#2563eb')->after('footer_text');
            $table->string('primary_color_light', 20)->default('#3b82f6')->after('primary_color');

            // Informasi tambahan sekolah
            $table->string('website', 200)->nullable()->after('email');
            $table->string('principal_name', 100)->nullable()->after('website');
            $table->string('accreditation', 20)->nullable()->after('principal_name');
            $table->year('established_year')->nullable()->after('accreditation');
            $table->text('vision')->nullable()->after('established_year');
            $table->text('mission')->nullable()->after('vision');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'portal_title', 'welcome_text', 'tagline', 'landing_image',
                'footer_text', 'primary_color', 'primary_color_light',
                'website', 'principal_name', 'accreditation',
                'established_year', 'vision', 'mission',
            ]);
        });
    }
};
