public function runQuoteTest() {
    $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin', 'slug' => 'admin']);
    $staffRole = \App\Models\Role::firstOrCreate(['name' => 'staff', 'slug' => 'staff']);
    $customerRole = \App\Models\Role::firstOrCreate(['name' => 'customer', 'slug' => 'customer']);

    $customer = \App\Models\User::firstOrCreate(
        ['email' => 'test2@test.com'],
        ['name' => 'Customer', 'password' => bcrypt('password'), 'role_id' => $customerRole->id, 'role' => 'customer', 'phone' => 'TestPhone']
    );

    $advisor = \App\Models\User::firstOrCreate(
        ['email' => 'advisor@test.com'],
        ['name' => 'Advisor', 'password' => bcrypt('password'), 'role_id' => $staffRole->id, 'role' => 'staff', 'phone' => 'TestAdvisorPhone']
    );

    $vehicle = \App\Models\Vehicle::firstOrCreate(
        ['license_plate' => 'TEST-123'],
        ['user_id' => $customer->id, 'owner_phone' => $customer->phone, 'make' => 'Toyota', 'model' => 'Camry', 'year' => 2022, 'color' => 'Black', 'type' => 'sedan']
    );

    $repairOrder = \App\Models\RepairOrder::create([
        'customer_id' => $customer->id,
        'advisor_id' => $advisor->id,
        'vehicle_id' => $vehicle->id,
        'status' => 'pending',
        'track_id' => 'TRK-TEST-' . rand(1000, 9999),
    ]);

    $task1 = \App\Models\RepairTask::create([
        'repair_order_id' => $repairOrder->id,
        'title' => 'Brake Check',
        'status' => 'pending',
    ]);
    
    $task2 = \App\Models\RepairTask::create([
        'repair_order_id' => $repairOrder->id,
        'title' => 'Oil Change',
        'status' => 'pending',
    ]);

    echo "Repair Order created: ID " . $repairOrder->id . "\n";
    echo "Tasks created.\n";
    echo "Done setup.\n";
}

runQuoteTest();
