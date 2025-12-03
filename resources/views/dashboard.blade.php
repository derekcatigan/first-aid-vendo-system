{{-- resources\views\dashboard.blade.php --}}
@extends('layout.layout')

@section('content')
    <div class="p-4 md:p-6">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Dashboard Overview</h1>
            <h3 class="text-gray-600">Monitor your first aid inventory and system activity</h3>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
            {{-- Total Items --}}
            <div class="p-5 rounded-sm border border-gray-300 bg-white shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7l9-4 9 4-9 4-9-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l9 4 9-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9 4 9-4" />
                        </svg>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total Items</p>
                        <p id="totalItems" class="text-3xl font-bold text-blue-700">{{ $totalItems }}</p>
                    </div>
                </div>
            </div>

            {{-- Low Stock --}}
            <div class="p-5 rounded-sm border border-gray-300 bg-white shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-.01-12a9 9 0 110 18 9 9 0 010-18z" />
                        </svg>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-600">Low Stock Items</p>
                        <p id="lowStockItems" class="text-3xl font-bold text-red-700">{{ $lowStockItems }}</p>
                    </div>
                </div>
            </div>

            {{-- Restocked Today --}}
            <div class="p-5 rounded-sm border border-gray-300 bg-white shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-600">Restocked Today</p>
                        <p id="restockedToday" class="text-3xl font-bold text-green-700">{{ $restockedToday }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Analytics + Recent Activity --}}
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Analytics Chart --}}
            <div class="bg-white p-4 border border-gray-300 shadow rounded-sm">
                <h2 class="text-lg font-bold mb-4">Inventory Analytics (This Month)</h2>
                <div class="relative" style="min-height:260px">
                    <canvas id="inventoryChart" class="w-full h-64"></canvas>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white p-4 border border-gray-300 shadow rounded-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-gray-100 border border-gray-300 rounded-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h3l1-1h4l1 1h3a2 2 0 012 2v14a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold">Recent Activity</h2>
                </div>

                <div class="rounded-sm border border-gray-200 shadow-sm max-h-[420px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Action</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Item</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Quantity</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Performed By</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Time</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($recentLogs as $log)
                                <tr class="text-sm hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-700">
                                        @if ($log->log_type === 'restock')
                                            <span class="text-green-700 font-semibold">Restocked</span>
                                        @elseif ($log->log_type === 'dispense')
                                            <span class="text-red-700 font-semibold">Dispensed</span>
                                        @else
                                            <span class="text-gray-600">{{ ucfirst($log->log_type) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->item->item_name ?? 'Unknown Item' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($log->quantity_change > 0)
                                            <span class="text-green-700 font-bold">+{{ $log->quantity_change }}</span>
                                        @else
                                            <span class="text-red-700 font-bold">{{ $log->quantity_change }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->user->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $log->created_at->format('M d, Y h:i A') }} — {{ $log->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-4">No recent activity found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Live Inventory Status --}}
        <div class="mt-10 bg-white p-4 border border-gray-300 shadow rounded-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-10 h-10 flex items-center justify-center bg-gray-100 border border-gray-300 rounded-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-700" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold">Live Inventory Status</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($items as $item)
                    <div class="p-5 rounded-md border border-gray-300 bg-white shadow-sm hover:shadow-md transition">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $item->item_name }}</h3>
                        <p class="text-sm text-gray-600 mb-3">{{ $item->description ?? 'No description available' }}</p>

                        <div class="flex justify-between items-center mt-3">
                            {{-- wrapper holds color class and data-threshold for JS --}}
                            <span id="qty-wrapper-{{ $item->id }}"
                                class="text-sm font-semibold {{ $item->quantity <= $item->low_stock_threshold ? 'text-red-600' : 'text-green-700' }}"
                                data-threshold="{{ $item->low_stock_threshold }}">
                                Quantity: <span id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                            </span>

                            @if (Auth::user()->role === 'admin')
                                <button class="btn btn-success btn-sm"
                                    onclick="openRestockModal({{ $item->id }}, '{{ addslashes($item->item_name) }}')">
                                    Restock
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Restock Modal --}}
    <dialog id="restockModal" class="modal">
        <form id="restockForm" method="dialog" class="modal-box" onsubmit="return false;">
            <h3 class="font-bold text-lg mb-3">Restock Item</h3>

            <p id="modalItemName" class="mb-3 font-medium text-gray-700"></p>

            <input type="hidden" id="restockItemId" />

            <label class="form-control w-full mb-3">
                <span class="label-text">Quantity to Add</span>
                <input id="restockQty" type="number" min="1" placeholder="Enter quantity" class="input input-bordered"
                    required />
            </label>

            <div class="modal-action">
                <button class="btn" formnovalidate type="button" onclick="closeRestockModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmRestock">Confirm</button>
            </div>
        </form>
    </dialog>
@endsection

@section('script')
    <script>
        // ---------- Helpers ----------
        function ajaxGet(url, success, error) {
            $.ajax({ url, method: 'GET' })
                .done(success)
                .fail(error || function () { console.error('GET failed:', url); });
        }

        function ajaxPost(url, data, success, error) {
            $.ajax({ url, method: 'POST', data })
                .done(success)
                .fail(error || function (xhr) { console.error('POST failed:', url, xhr); });
        }

        // ---------- Modal helpers ----------
        function openRestockModal(itemId, itemName) {
            $('#restockItemId').val(itemId);
            $('#modalItemName').text('Item: ' + itemName);
            $('#restockQty').val('');
            const dlg = $('#restockModal')[0];
            if (typeof dlg.showModal === 'function') dlg.showModal();
            else $('#restockModal').addClass('modal-open'); // fallback (daisyui)
        }

        function closeRestockModal() {
            const dlg = $('#restockModal')[0];
            if (typeof dlg.close === 'function') dlg.close();
            else $('#restockModal').removeClass('modal-open'); // fallback
        }

        // ---------- Refresh summary cards ----------
        function refreshDashboardSummary() {
            ajaxGet("{{ route('dashboard.summary') }}", function (response) {
                $('#totalItems').text(response.totalItems);
                $('#lowStockItems').text(response.lowStockItems);
                $('#restockedToday').text(response.restockedToday);
            });
        }

        // ---------- Refresh chart ----------
        function loadInventoryAnalytics() {
            ajaxGet("{{ route('dashboard.analytics') }}", function (response) {
                const canvas = document.getElementById('inventoryChart');
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                const labels = response.labels || [];
                const restock = response.restock || [];
                const dispense = response.dispense || [];

                if (window.inventoryChart instanceof Chart) {
                    window.inventoryChart.data.labels = labels;
                    window.inventoryChart.data.datasets[0].data = restock;
                    window.inventoryChart.data.datasets[1].data = dispense;
                    window.inventoryChart.update();
                } else {
                    window.inventoryChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                { label: 'Restocked', data: restock, borderColor: 'green', backgroundColor: 'rgba(0,128,0,0.08)', fill: true, tension: 0.25 },
                                { label: 'Dispensed', data: dispense, borderColor: 'red', backgroundColor: 'rgba(255,0,0,0.08)', fill: true, tension: 0.25 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            });
        }

        // ---------- Refresh recent activity table ----------
        function refreshRecentLogs() {
            ajaxGet("{{ route('dashboard.recentLogs') }}", function (logs) {
                const tbody = $('table tbody');
                tbody.empty();

                if (!logs.length) {
                    tbody.append('<tr><td colspan="5" class="text-center text-gray-500 py-4">No recent activity found.</td></tr>');
                    return;
                }

                logs.forEach(log => {
                    const action = log.log_type === 'restock'
                        ? '<span class="text-green-700 font-semibold">Restocked</span>'
                        : log.log_type === 'dispense'
                            ? '<span class="text-red-700 font-semibold">Dispensed</span>'
                            : `<span class="text-gray-600">${log.log_type.charAt(0).toUpperCase() + log.log_type.slice(1)}</span>`;

                    const itemName = log.item?.item_name || 'Unknown Item';
                    const quantity = log.quantity_change > 0
                        ? `<span class="text-green-700 font-bold">+${log.quantity_change}</span>`
                        : `<span class="text-red-700 font-bold">${log.quantity_change}</span>`;
                    const user = log.user?.name || 'System';
                    const time = log.created_at_formatted; // use the pre-formatted string

                    tbody.append(`
                                            <tr class="text-sm hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 font-medium text-gray-700">${action}</td>
                                                <td class="px-4 py-3 text-gray-700">${itemName}</td>
                                                <td class="px-4 py-3">${quantity}</td>
                                                <td class="px-4 py-3 text-gray-700">${user}</td>
                                                <td class="px-4 py-3 text-gray-700">${time}</td>
                                            </tr>
                                        `);
                });
            });
        }

        // ---------- Restock handler ----------
        $(document).ready(function () {
            // Initialize chart and table on page load
            loadInventoryAnalytics();
            refreshRecentLogs();

            $('#confirmRestock').on('click', function () {
                const itemId = $('#restockItemId').val();
                const quantityToAdd = parseInt($('#restockQty').val(), 10);

                if (!quantityToAdd || quantityToAdd <= 0) {
                    Swal.fire({
                        toast: true,
                        icon: 'warning',
                        title: 'Please enter a valid quantity',
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }

                ajaxPost(`/items/${itemId}/restock`, {
                    _token: "{{ csrf_token() }}",
                    amount: quantityToAdd
                }, function (response) {
                    if (response?.success) {
                        // Update quantity and color
                        const wrapper = $(`#qty-wrapper-${itemId}`);
                        $(`#qty-${itemId}`).text(response.newQuantity);
                        wrapper.removeClass('text-green-700 text-red-600');
                        wrapper.addClass(response.newQuantity <= parseInt(wrapper.data('threshold')) ? 'text-red-600' : 'text-green-700');

                        closeRestockModal();

                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Item restocked successfully',
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Refresh everything
                        refreshDashboardSummary();
                        loadInventoryAnalytics();
                        refreshRecentLogs();
                    } else {
                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: response?.message || 'Failed to restock',
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }, function (xhr) {
                    const msg = xhr?.responseJSON?.message || 'Server error';
                    Swal.fire({
                        toast: true,
                        icon: 'error',
                        title: msg,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        });
    </script>
@endsection