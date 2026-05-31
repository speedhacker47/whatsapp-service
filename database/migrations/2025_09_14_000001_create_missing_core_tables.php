<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all tables that were originally created by the Corbital installer
     * but are missing from the standard migration files.
     */
    public function up(): void
    {
        // ─── subscriptions ───────────────────────────────────────────────────
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('plan_id');
                $table->string('status')->default('new'); // new, active, paused, cancelled, ended
                $table->timestamp('current_period_ends_at')->nullable();
                $table->timestamp('trial_starts_at')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->boolean('is_recurring')->default(false);
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamp('terminated_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->integer('payment_attempt_count')->default(0);
                $table->timestamp('last_payment_attempt_at')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('plan_id');
                $table->index('status');
            });
        }

        // ─── invoices ────────────────────────────────────────────────────────
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->string('type')->default('new_subscription'); // new_subscription, renew_subscription, change_plan
                $table->string('status')->default('unpaid'); // unpaid, paid, cancelled
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('currency_id');
                $table->decimal('total_tax_amount', 10, 2)->default(0);
                $table->decimal('fee', 10, 2)->default(0);
                $table->string('invoice_number')->nullable();
                $table->timestamp('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->boolean('no_payment_required_when_free')->default(false);
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('subscription_id');
                $table->index('status');
            });
        }

        // ─── invoice_items ───────────────────────────────────────────────────
        if (! Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('description');
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->integer('quantity')->default(1);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->timestamps();

                $table->index('invoice_id');
            });
        }

        // ─── invoice_taxes ───────────────────────────────────────────────────
        if (! Schema::hasTable('invoice_taxes')) {
            Schema::create('invoice_taxes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('tax_id')->nullable();
                $table->string('name');
                $table->decimal('rate', 8, 4)->default(0);
                $table->decimal('amount', 10, 2)->default(0);
                $table->timestamps();

                $table->index('invoice_id');
            });
        }

        // ─── transactions ────────────────────────────────────────────────────
        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('payment_method_id')->nullable();
                $table->string('type')->default('payment'); // payment, refund
                $table->string('status')->default('pending'); // pending, success, failed
                $table->decimal('amount', 10, 2)->default(0);
                $table->unsignedBigInteger('currency_id');
                $table->text('description')->nullable();
                $table->text('error')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('status');
                $table->index('created_at');
            });
        }

        // ─── credit_transactions ─────────────────────────────────────────────
        if (! Schema::hasTable('credit_transactions')) {
            Schema::create('credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('type'); // credit, debit
                $table->decimal('amount', 10, 2)->default(0);
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // ─── tenant_credit_balances ──────────────────────────────────────────
        if (! Schema::hasTable('tenant_credit_balances')) {
            Schema::create('tenant_credit_balances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->unique();
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->decimal('balance', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        // ─── payment_methods ─────────────────────────────────────────────────
        if (! Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('driver'); // stripe, paypal, razorpay, paystack, manual
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        // ─── subscription_logs ───────────────────────────────────────────────
        if (! Schema::hasTable('subscription_logs')) {
            Schema::create('subscription_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->string('event'); // created, activated, cancelled, renewed, plan_changed
                $table->json('data')->nullable();
                $table->timestamps();

                $table->index('subscription_id');
            });
        }

        // ─── contacts (global, not tenant-specific) ──────────────────────────
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->string('company')->nullable();
                $table->string('type')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->string('zip')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->text('address')->nullable();
                $table->unsignedBigInteger('assigned_id')->nullable();
                $table->unsignedBigInteger('status_id')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->unsignedBigInteger('addedfrom')->nullable();
                $table->timestamp('dateassigned')->nullable();
                $table->timestamp('last_status_change')->nullable();
                $table->string('default_language')->nullable();
                $table->json('group_id')->nullable();
                $table->json('custom_fields')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('phone');
            });
        }

        // ─── contact_groups ──────────────────────────────────────────────────
        if (! Schema::hasTable('contact_groups')) {
            Schema::create('contact_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contact_id');
                $table->unsignedBigInteger('group_id');
                $table->timestamps();

                $table->index(['contact_id', 'group_id']);
            });
        }

        // ─── whatsapp_settings ───────────────────────────────────────────────
        if (! Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('waba_id')->nullable();
                $table->string('phone_number_id')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('access_token', 512)->nullable();
                $table->string('app_id')->nullable();
                $table->string('app_secret', 256)->nullable();
                $table->string('webhook_verify_token')->nullable();
                $table->string('fb_config_id')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // ─── whatsapp_templates ──────────────────────────────────────────────
        if (! Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('template_id');
                $table->string('template_name');
                $table->string('language')->default('en_US');
                $table->string('status')->default('APPROVED');
                $table->string('category')->default('MARKETING');
                $table->string('header_data_format')->nullable();
                $table->text('header_data_text')->nullable();
                $table->integer('header_params_count')->nullable();
                $table->text('body_data');
                $table->integer('body_params_count')->nullable();
                $table->text('footer_data')->nullable();
                $table->integer('footer_params_count')->nullable();
                $table->text('buttons_data')->nullable();
                $table->json('allow_category_change')->nullable();
                $table->json('quality_score')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // ─── broadcasts ──────────────────────────────────────────────────────
        if (! Schema::hasTable('broadcasts')) {
            Schema::create('broadcasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('status')->default('pending'); // pending, running, completed, failed
                $table->string('template_id')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->integer('total_count')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // ─── broadcast_contacts ──────────────────────────────────────────────
        if (! Schema::hasTable('broadcast_contacts')) {
            Schema::create('broadcast_contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('broadcast_id');
                $table->unsignedBigInteger('contact_id')->nullable();
                $table->string('phone')->nullable();
                $table->string('status')->default('pending');
                $table->text('error')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index('broadcast_id');
            });
        }

        // ─── campaign_logs ────────────────────────────────────────────────────
        if (! Schema::hasTable('campaign_logs')) {
            Schema::create('campaign_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('campaign_id')->nullable();
                $table->string('type')->default('info');
                $table->text('message');
                $table->json('context')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'campaign_id']);
            });
        }

        // ─── api_tokens ──────────────────────────────────────────────────────
        if (! Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->string('token', 80)->unique();
                $table->json('abilities')->nullable();
                $table->json('rate_limits')->nullable();
                $table->integer('monthly_quota')->nullable();
                $table->integer('usage_current_month')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        // ─── notifications ───────────────────────────────────────────────────
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // ─── activity_log ─────────────────────────────────────────────────────
        if (! Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();

                $table->index('log_name');
            });
        }

        // ─── media ───────────────────────────────────────────────────────────
        if (! Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->morphs('model');
                $table->uuid('uuid')->nullable()->unique();
                $table->string('collection_name');
                $table->string('name');
                $table->string('file_name');
                $table->string('mime_type')->nullable();
                $table->string('disk');
                $table->string('conversions_disk')->nullable();
                $table->unsignedBigInteger('size');
                $table->json('manipulations');
                $table->json('custom_properties');
                $table->json('generated_conversions');
                $table->json('responsive_images');
                $table->unsignedInteger('order_column')->nullable()->index();
                $table->nullableTimestamps();
            });
        }

        // ─── tickets ─────────────────────────────────────────────────────────
        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('tenant_staff_id')->nullable();
                $table->string('subject');
                $table->unsignedBigInteger('department_id')->nullable();
                $table->json('assignee_id')->nullable();
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->string('status')->default('open'); // open, pending, answered, closed
                $table->string('ticket_id')->unique();
                $table->boolean('admin_viewed')->default(false);
                $table->boolean('tenant_viewed')->default(false);
                $table->json('attachments')->nullable();
                $table->text('body');
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('status');
                $table->index('department_id');
            });
        }

        // ─── ticket_replies ──────────────────────────────────────────────────
        if (! Schema::hasTable('ticket_replies')) {
            Schema::create('ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_type')->default('admin'); // admin, tenant
                $table->json('attachments')->nullable();
                $table->boolean('viewed')->default(false);
                $table->integer('send_notification')->nullable();
                $table->text('content');
                $table->timestamps();

                $table->index('ticket_id');
            });
        }

        // ─── ticket_attachments ──────────────────────────────────────────────
        if (! Schema::hasTable('ticket_attachments')) {
            Schema::create('ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->unsignedBigInteger('reply_id')->nullable();
                $table->string('filename');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->string('path');
                $table->timestamps();

                $table->index('ticket_id');
            });
        }

        // ─── feature_usages ──────────────────────────────────────────────────
        if (! Schema::hasTable('feature_usages')) {
            Schema::create('feature_usages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('feature_id');
                $table->integer('used')->default(0);
                $table->timestamp('reset_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'feature_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ticket_attachments',
            'ticket_replies',
            'tickets',
            'media',
            'activity_log',
            'notifications',
            'api_tokens',
            'campaign_logs',
            'broadcast_contacts',
            'broadcasts',
            'whatsapp_templates',
            'whatsapp_settings',
            'contact_groups',
            'contacts',
            'subscription_logs',
            'payment_methods',
            'tenant_credit_balances',
            'credit_transactions',
            'transactions',
            'invoice_taxes',
            'invoice_items',
            'invoices',
            'subscriptions',
            'feature_usages',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
