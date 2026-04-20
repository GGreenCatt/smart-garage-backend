<?php
function testEndpoints() {
    $advisor = \App\Models\User::where('email', 'advisor@test.com')->first();
    $customer = \App\Models\User::where('email', 'test2@test.com')->first();
    $ro = \App\Models\RepairOrder::orderBy('id', 'desc')->first();

    echo "--- STAFF SEND QUOTE ---\n";
    $request = \Illuminate\Http\Request::create("/staff/order/{$ro->id}/send-quote", 'POST');
    $request->setUserResolver(function () use ($advisor) {
        return $advisor;
    });

    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo $response->getContent() . "\n";
    
    echo "\n--- CUSTOMER VIEW QUOTE ---\n";
    $request2 = \Illuminate\Http\Request::create("/customer/order/{$ro->id}/quote", 'GET');
    $request2->setUserResolver(function () use ($customer) {
        return $customer;
    });
    
    $response2 = app()->handle($request2);
    echo "Status: " . $response2->getStatusCode() . "\n";
    echo substr($response2->getContent(), 0, 150) . "...(truncated)\n";

    echo "\n--- CUSTOMER APPROVE/REJECT ---\n";
    $tasks = $ro->tasks;
    $payload = [
        'tasks' => [
            ['id' => $tasks[0]->id, 'status' => 'approved'],
            ['id' => $tasks[1]->id, 'status' => 'rejected'],
        ]
    ];
    $request3 = \Illuminate\Http\Request::create("/customer/order/{$ro->id}/quote/tasks", 'POST', $payload);
    $request3->headers->set('Accept', 'application/json');
    $request3->setUserResolver(function () use ($customer) {
        return $customer;
    });
    
    $response3 = app()->handle($request3);
    echo "Status: " . $response3->getStatusCode() . "\n";
    echo $response3->getContent() . "\n";
}

testEndpoints();
