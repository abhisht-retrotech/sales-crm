<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tenant = \App\Models\Tenant::find(2) ?? \App\Models\Tenant::first();
    if ($tenant) {
        $tenant->connect();
        app()->instance('currentTenant', $tenant);
    }
    
    $controller = app(\App\Http\Controllers\AdminController::class);
    $req = \Illuminate\Http\Request::create('/admin/sharing-rules', 'GET');
    
    echo "1. Testing sharingRules view...\n";
    $view = $controller->sharingRules($req);
    $content = $view->render();
    echo "   OK! Sharing rules view rendered. Bytes: " . strlen($content) . "\n";

    echo "2. Testing getSharingRulesMeta...\n";
    $metaRes = $controller->getSharingRulesMeta();
    echo "   OK! Meta status: " . $metaRes->getStatusCode() . ", bytes: " . strlen($metaRes->getContent()) . "\n";

    echo "3. Testing getSharingRules API...\n";
    $rulesRes = $controller->getSharingRules($req);
    echo "   OK! Rules status: " . $rulesRes->getStatusCode() . ", bytes: " . strlen($rulesRes->getContent()) . "\n";

    echo "4. Testing approvalRules view...\n";
    $appReq = \Illuminate\Http\Request::create('/admin/approval-rules', 'GET');
    $appView = $controller->approvalRules($appReq);
    $appContent = $appView->render();
    echo "   OK! Approval rules view rendered. Bytes: " . strlen($appContent) . "\n";

    echo "\n>>> ALL TESTS PASSED SUCCESSFULLY! <<<\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
}
