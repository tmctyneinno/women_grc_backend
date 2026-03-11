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
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('enrollment_type', ['open', 'invite_only', 'premium'])
                ->default('open')
                ->after('status');
            $table->enum('navigation_mode', ['free', 'locked'])
                ->default('free')
                ->after('enrollment_type');
            $table->unsignedTinyInteger('passing_threshold')
                ->default(70)
                ->after('navigation_mode');
            $table->boolean('requires_quiz_pass')
                ->default(false)
                ->after('passing_threshold');
            $table->boolean('is_active')
                ->default(true)
                ->after('requires_quiz_pass');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('position');
            $table->boolean('require_quiz_to_unlock')->default(false)->after('is_active');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer'])
                ->default('multiple_choice')
                ->after('question');
            $table->unsignedTinyInteger('passing_threshold')->default(70)->after('correct_answer');
            $table->unsignedTinyInteger('max_attempts')->default(3)->after('passing_threshold');
            $table->boolean('show_feedback')->default(true)->after('max_attempts');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('verification_code')->nullable()->unique()->after('certificate_code');
            $table->string('qr_code')->nullable()->after('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'qr_code']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'question_type',
                'passing_threshold',
                'max_attempts',
                'show_feedback',
            ]);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'require_quiz_to_unlock']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'enrollment_type',
                'navigation_mode',
                'passing_threshold',
                'requires_quiz_pass',
                'is_active',
            ]);
        });
    }
};

