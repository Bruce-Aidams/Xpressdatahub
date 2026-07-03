<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_config', function (Blueprint $table) {
            $table->string('api_name')->nullable()->after('network_type');
            $table->string('status_endpoint')->nullable()->after('endpoint_url');
            $table->string('request_method', 10)->default('POST')->after('api_secret');
            $table->text('request_headers')->nullable()->after('request_method');
            $table->longText('request_body_template')->nullable()->after('request_headers');
            $table->string('response_success_field')->default('success')->after('request_body_template');
            $table->string('response_data_field')->default('data')->after('response_success_field');
            $table->string('response_error_field')->default('error')->after('response_data_field');
            $table->integer('timeout_seconds')->default(30)->after('response_error_field');
            $table->integer('retry_attempts')->default(3)->after('timeout_seconds');
        });

        // Migrate existing config_data into new flat columns
        $configs = DB::table('api_config')->get();
        foreach ($configs as $config) {
            $cd = is_string($config->config_data) ? json_decode($config->config_data, true) : ($config->config_data ?? []);
            DB::table('api_config')->where('id', $config->id)->update([
                'api_name' => $config->network_type.' API',
                'request_method' => $cd['request_method'] ?? 'POST',
                'request_headers' => is_array($cd['request_headers'] ?? null) ? json_encode($cd['request_headers']) : ($cd['request_headers'] ?? null),
                'request_body_template' => $cd['request_body_template'] ?? null,
                'response_success_field' => $cd['response_success_field'] ?? 'success',
                'response_data_field' => $cd['response_data_field'] ?? 'data',
                'response_error_field' => $cd['response_error_field'] ?? 'error',
                'timeout_seconds' => $cd['timeout_seconds'] ?? 30,
                'retry_attempts' => $cd['retry_attempts'] ?? 3,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('api_config', function (Blueprint $table) {
            $table->dropColumn([
                'api_name', 'status_endpoint', 'request_method', 'request_headers',
                'request_body_template', 'response_success_field', 'response_data_field',
                'response_error_field', 'timeout_seconds', 'retry_attempts',
            ]);
        });
    }
};
