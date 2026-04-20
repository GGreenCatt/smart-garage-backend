@props(['order', 'status'])
<!-- Not actively used in the spatial map view but kept for fallback or list views -->
<div class="bg-white p-4 rounded shadow">
    {{ $order->vehicle->license_plate }}
</div>
