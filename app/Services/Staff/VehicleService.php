<?php

namespace App\Services\Staff;

use App\Models\Vehicle;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class VehicleService
{
    /**
     * Create or update a vehicle based on input.
     * Logic moved from StaffController::storeVehicle
     */
    public function store(array $data): Vehicle
    {
        $phone = preg_replace('/[^0-9]/', '', $data['owner_phone']);
        $plate = strtoupper(trim($data['license_plate']));

        // Check if owner exists by phone, if not create new Customer User
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $data['owner_name'],
                'password' => Hash::make('12345678'), // Default password
                'email' => $data['email'] ?? ($phone . '@noemail.com'), // Auto-generate if missing
                'role' => 'customer',
                'role_id' => Role::where('slug', 'customer')->value('id'),
            ]
        );

        if ($user->name !== $data['owner_name'] && empty($user->name)) {
            $user->update(['name' => $data['owner_name']]);
        }

        $userId = $user->id;

        $normalizedPlate = preg_replace('/[^A-Z0-9]/', '', $plate);
        $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', '') = ?", [$normalizedPlate])->first();

        $payload = [
            'user_id' => $userId,
            'license_plate' => $plate,
            'model' => $data['model'],
            'type' => $data['type'],
            'year' => $data['year'] ?? date('Y'),
            'color' => $data['color'] ?? 'Unknown',
            'vin' => $data['vin'] ?? null,
            'owner_name' => $data['owner_name'],
            'owner_phone' => $phone,
        ];

        if ($vehicle) {
            $vehicle->update($payload);
            return $vehicle;
        }

        return Vehicle::create($payload);
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
