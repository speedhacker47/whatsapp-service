<?php
require '/var/www/html/chatbot/vendor/autoload.php';
$app = require '/var/www/html/chatbot/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TABLES THAT EXIST IN DB ===\n";
$existing = DB::select('SHOW TABLES');
$existingTables = [];
foreach ($existing as $row) {
    $cols = (array)$row;
    $name = reset($cols);
    $existingTables[] = $name;
    echo "  $name\n";
}

echo "\n=== CHECKING KEY TABLES NEEDED BY APP ===\n";
$needed = [
    'transactions',
    'subscriptions', 
    'invoices',
    'tickets',
    'ticket_replies',
    'ticket_attachments',
    'contacts',
    'whatsapp_templates',
    'whatsapp_settings',
    'bot_flows',
    'groups',
    'contact_groups',
    'broadcasts',
    'broadcast_contacts',
    'campaign_logs',
    'api_tokens',
    'notifications',
    'activity_log',
    'media',
    'pages',
    'plans',
    'features',
    'plan_features',
    'currencies',
    'tenants',
    'settings',
    'email_templates',
    'email_logs',
    'email_layouts',
    'languages',
    'modules',
    'custom_fields',
    'contact_imports',
    'faqs',
    'departments',
    'taxes',
];

$missing = [];
foreach ($needed as $table) {
    if (!in_array($table, $existingTables)) {
        $missing[] = $table;
        echo "  MISSING: $table\n";
    } else {
        echo "  OK: $table\n";
    }
}

echo "\n=== MISSING TABLES ===\n";
foreach ($missing as $t) {
    echo "  $t\n";
}
echo "\nTotal missing: " . count($missing) . "\n";
