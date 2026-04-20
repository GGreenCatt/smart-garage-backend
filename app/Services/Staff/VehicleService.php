<?php

namespace App\Services\Staff;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    /**
     * Create or update a vehicle based on input.
     * Logic moved from StaffController::storeVehicle
     */
    public function store(array $data): Vehicle
    {
        // Check if owner exists by phone, if not create new Customer User
        $user = User::firstOrCreate(
            ['phone' => $data['owner_phone']],
            [
                'name' => $data['owner_name'],
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'), // Default password
                'email' => $data['email'] ?? ($data['owner_phone'] . '@noemail.com'), // Auto-generate if missing
                'role' => 'customer' // Default role
            ]
        );
        $userId = $user->id;

        // Create or Update
        return Vehicle::updateOrCreate(
            [
                'license_plate' => $data['license_plate']
            ],
            [
                'user_id' => $userId,
                'model' => $data['model'],
                'type' => $data['type'], // 'car' or 'motorcycle'
                'year' => $data['year'] ?? date('Y'),
                'color' => $data['color'] ?? 'Unknown',
                'vin' => $data['vin'] ?? null,
                'status' => 'active',
                'owner_name' => $data['owner_name'],
                'owner_phone' => $data['owner_phone'],
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    /**
     * Update an existing vehicle.
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        // Similar user check might be needed if phone changed, 
        // but typically update handles direct fields.
        // Assuming we update ownership if phone changes:
        if (isset($data['owner_phone'])) {
            $user = User::where('phone', $data['owner_phone'])->first();
            $data['user_id'] = $user ? $user->id : null;
        }

        $vehicle->update($data);
        return $vehicle;
    }

    /**
     * Delete a vehicle.
     */
    public function delete(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }

    /**
     * Get vehicles for a customer (by ID or Phone).
     */
    public function getByCustomer(int $userId): Collection
    {
        $customer = User::findOrFail($userId);
        
        return Vehicle::where('user_id', $customer->id)
            ->orWhere('owner_phone', $customer->phone)
            ->get();
    }

    /**
     * Get vehicles by Phone number.
     */
    public function getByPhone(string $phone): Collection
    {
        return Vehicle::where('owner_phone', $phone)->get();
    }
}
