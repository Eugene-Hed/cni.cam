<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/auth/register', 'POST', [
    'nom' => 'Test', 'prenom' => 'Test', 'date_naissance' => '2000-01-01', 'genre' => 'M',
    'email' => 'test@test.com', 'telephone' => '600000000', 'profession' => 'Test', 'adresse' => 'Test',
    'region_naissance_id' => '1', 'departement_naissance_id' => '', 'ville_naissance_id' => 'Yaounde',
    'region_residence_id' => '1', 'departement_residence_id' => '', 'ville_residence_id' => '',
    'ethnie_id' => '1', 'force_creation' => false
]);

try {
    app()->make(App\Http\Requests\Auth\RegisterRequest::class)->setContainer(app())->setRedirector(app()->make(Illuminate\Routing\Redirector::class))->validateResolved();
    echo "Validation Passed \n";
} catch (Illuminate\Validation\ValidationException $e) {
    print_r($e->errors());
}
