@extends('layout.layout')

@section('content')
    <section class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">

        {{-- ================= HEADER ================= --}}
        <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="p-3 rounded-lg bg-blue-100 text-blue-700">
                <i class="fa-solid fa-box-open text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Inventory Management</h1>
                <p class="text-sm text-gray-500">
                    Manage vending machine inventory and barangay stocks
                </p>
            </div>
        </div>

        {{-- ================= MAIN GRID ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ========== ADD BARANGAY STOCK ========= --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Add Barangay Stock</h3>
                <form id="barangayForm" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500">Item Name</label>
                        <input type="text" name="itemName" class="input input-bordered w-full" placeholder="Cotton Balls"
                            required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Description</label>
                        <textarea name="itemDescription" class="textarea textarea-bordered w-full"
                            placeholder="Short description"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Quantity</label>
                            <input type="number" name="itemQuantity" class="input input-bordered w-full" min="0" value="0"
                                required>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Low Stock Alert</label>
                            <input type="number" name="itemLowStock" class="input input-bordered w-full" min="0" value="0">
                        </div>
                    </div>
                    <button class="btn btn-primary w-full">Add Stock</button>
                </form>
            </div>

            {{-- ========== VENDING MACHINE INVENTORY ========= --}}
            <div class="lg:col-span-2 bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Vending Machine Inventory</h3>
                <div class="overflow-auto max-h-80 rounded-lg border border-gray-300">
                    <table class="table table-sm w-full">
                        <thead class="sticky top-0 bg-gray-100 text-xs text-gray-600">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Keypad</th>
                                <th>Motor</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody">
                            @forelse ($items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="font-medium data-cell">{{ $item->item_name }}</td>
                                    <td class="data-cell">{{ $item->quantity }}</td>
                                    <td class="data-cell">{{ $item->keypad }}</td>
                                    <td class="data-cell">{{ $item->motor_index }}</td>
                                    <td class="text-center space-x-1">
                                        <button
                                            class="toggleBtn btn btn-xs {{ $item->is_active ? 'btn-outline btn-warning' : 'btn-success animate-pulse shadow-md' }}"
                                            data-id="{{ $item->id }}">
                                            @if($item->is_active)
                                                <i class="fa-solid fa-ban mr-1"></i> Disable
                                            @else
                                                <i class="fa-solid fa-play mr-1"></i> Enable
                                            @endif
                                        </button>
                                        <button
                                            class="btn btn-xs btn-warning deductItemBtn"
                                            data-id="{{ $item->id }}"
                                            title="Deduct 1"
                                        >
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                        <button class="btn btn-xs btn-ghost text-error deleteBtn" data-id="{{ $item->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-items-row">
                                    <td colspan="5" class="text-center text-gray-400 py-6">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ========== BARANGAY STOCKS ========= --}}
            <div class="lg:col-span-3 bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Barangay Stocks</h3>
                <div class="overflow-auto max-h-[300px] rounded-lg border border-gray-300">
                    <table class="table table-sm w-full">
                        <thead class="sticky top-0 bg-gray-100 text-xs text-gray-600">
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="barangayTableBody">
                            @forelse ($barangayStocks as $bItem)
                                <tr id="barangay-row-{{ $bItem->id }}">
                                    <td class="font-medium">{{ $bItem->item_name }}</td>
                                    <td id="barangay-qty-{{ $bItem->id }}">{{ $bItem->quantity }}</td>
                                    <td class="text-center space-x-1">
                                        <button class="btn btn-xs btn-info restockBtn" data-id="{{ $bItem->id }}"
                                            data-name="{{ $bItem->item_name }}">
                                            <i class="fa-solid fa-plus"></i> Restock
                                        </button>
                                        <button class="btn btn-xs btn-success transferBtn"
                                            data-id="{{ $bItem->id }}">Transfer</button>
                                        <button class="btn btn-xs btn-warning deductBarangayBtn" data-id="{{ $bItem->id }}" title="Deduct 1">
                                                <i class="fa-solid fa-minus"></i>
                                            </button>
                                        <button class="btn btn-xs btn-error deleteBarangayBtn" data-id="{{ $bItem->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-barangay-items">
                                    <td colspan="3" class="text-center text-gray-400 py-6">No barangay items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= MODALS ================= --}}
    <input type="checkbox" id="restockModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-3">Restock: <span id="restockItemName"></span></h3>
            <form id="restockForm" class="space-y-3">
                @csrf
                <input type="hidden" id="restockId">
                <div>
                    <label class="text-xs text-gray-500">Additional Quantity</label>
                    <input type="number" id="restockQuantity" class="input input-bordered w-full" min="1" required>
                </div>
                <div class="modal-action">
                    <label for="restockModal" class="btn btn-ghost">Cancel</label>
                    <button class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    <input type="checkbox" id="transferModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-3">Transfer to Vending Machine</h3>
            <form id="transferForm" class="space-y-3">
                @csrf
                <input type="hidden" id="transferBarangayId">
                <div>
                    <label class="text-xs text-gray-500">Quantity</label>
                    <input type="number" id="transferQuantity" class="input input-bordered w-full" min="1" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">Keypad (1-8)</label>
                        <input type="number" id="transferKeypad" class="input input-bordered w-full" min="1" max="8"
                            required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Motor Index (1-8)</label>
                        <input type="number" id="transferMotor" class="input input-bordered w-full" min="1" max="8"
                            required>
                    </div>
                </div>
                <div class="modal-action">
                    <label for="transferModal" class="btn btn-ghost">Cancel</label>
                    <button class="btn btn-primary">Transfer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            const showToastError = (msg) => Toast.fire({ icon: "error", title: msg });

            $(document).on("click", ".deductItemBtn", function () {
                const id = $(this).data("id");

                $.ajax({
                    url: `/item/${id}/deduct`,
                    type: "PATCH",
                    data: { _token: "{{ csrf_token() }}" },
                    success: (res) => {
                        Toast.fire({ icon: "success", title: res.message });
                        location.reload();
                    },
                    error: (xhr) => {
                        showToastError(xhr.responseJSON?.message || "Deduction failed");
                    }
                });
            });

            $(document).on("click", ".deductBarangayBtn", function () {
                const id = $(this).data("id");

                $.ajax({
                    url: `/barangay-stock/${id}/deduct`,
                    type: "PATCH",
                    data: { _token: "{{ csrf_token() }}" },
                    success: (res) => {
                        Toast.fire({ icon: "success", title: res.message });
                        $(`#barangay-qty-${id}`).text(res.new_quantity);
                    },
                    error: (xhr) => {
                        showToastError(xhr.responseJSON?.message || "Deduction failed");
                    }
                });
            });

            // --- Barangay Stock Add ---
            $("#barangayForm").on("submit", function (e) {
                e.preventDefault();
                $.post("{{ route('barangay.store') }}", $(this).serialize(), function (res) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $(".no-barangay-items").remove();
                    $("#barangayTableBody").append(`
                            <tr id="barangay-row-${res.barangayItem.id}">
                                <td class="font-medium">${res.barangayItem.item_name}</td>
                                <td id="barangay-qty-${res.barangayItem.id}">${res.barangayItem.quantity}</td>
                                <td class="text-center space-x-1">
                                    <button class="btn btn-xs btn-info restockBtn" data-id="${res.barangayItem.id}" data-name="${res.barangayItem.item_name}"><i class="fa-solid fa-plus"></i> Restock</button>
                                    <button class="btn btn-xs btn-success transferBtn" data-id="${res.barangayItem.id}">Transfer</button>
                                    <button
                                        class="btn btn-xs btn-warning deductBarangayBtn"
                                        data-id="${res.barangayItem.id}"
                                        title="Deduct 1"
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <button class="btn btn-xs btn-error deleteBarangayBtn" data-id="${res.barangayItem.id}"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    $("#barangayForm")[0].reset();
                }).fail(() => showToastError('Failed to add barangay stock'));
            });

            // --- Restock ---
            $(document).on("click", ".restockBtn", function () {
                $("#restockId").val($(this).data('id'));
                $("#restockItemName").text($(this).data('name'));
                $("#restockModal").prop("checked", true);
            });

            $("#restockForm").on("submit", function (e) {
                e.preventDefault();
                const id = $("#restockId").val();
                $.post(`/barangay-stock/${id}/restock`, {
                    _token: "{{ csrf_token() }}",
                    quantity: $("#restockQuantity").val()
                }, function (res) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $(`#barangay-qty-${id}`).text(res.new_quantity);
                    $("#restockModal").prop("checked", false);
                    $("#restockForm")[0].reset();
                }).fail(() => showToastError('Restock failed'));
            });

            // --- Transfer ---
            $(document).on("click", ".transferBtn", function () {
                $("#transferBarangayId").val($(this).data('id'));
                $("#transferModal").prop("checked", true);
            });

            $("#transferForm").on("submit", function (e) {
                e.preventDefault();
                const id = $("#transferBarangayId").val();
                const data = {
                    _token: "{{ csrf_token() }}",
                    quantity: $("#transferQuantity").val(),
                    keypad: $("#transferKeypad").val(),
                    motor_index: $("#transferMotor").val(),
                };

                $.post(`/barangay-stock/transfer/${id}`, data, function (res) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $(`#barangay-qty-${id}`).text(res.remainingBarangayQty);
                    location.reload(); // Simple way to sync Vendo table, or append manually if preferred
                }).fail(xhr => showToastError(xhr.responseJSON?.message || 'Transfer failed'));
            });

            // --- Toggle Vendo Status ---
            $(document).on("click", ".toggleBtn", function () {
                const btn = $(this);
                $.ajax({
                    url: `/item/${btn.data("id")}/toggle-status`,
                    type: "PATCH",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        Toast.fire({ icon: "success", title: res.message });
                        location.reload();
                    },
                    error: xhr => showToastError(xhr.responseJSON?.message || "Action failed")
                });
            });

            // --- Delete Vendo Item ---
            $(document).on("click", ".deleteBtn", function () {
                const id = $(this).data("id");
                const row = $(this).closest("tr");

                Swal.fire({
                    title: "Delete from Vendo?",
                    text: "This removes it from the machine inventory.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    confirmButtonText: "Yes, delete"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/item/" + id,
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: (res) => {
                                Toast.fire({ icon: 'success', title: res.message });
                                row.fadeOut(300, () => row.remove());
                            },
                            error: (xhr) => {
                                // Check if it's the quantity validation error (422)
                                if (xhr.status === 422) {
                                    Swal.fire({
                                        title: "Stock Remaining!",
                                        text: xhr.responseJSON.message,
                                        icon: "error"
                                    });
                                } else {
                                    // Generic error for 500s or other issues
                                    Toast.fire({
                                        icon: 'error',
                                        title: xhr.responseJSON?.message || 'Failed to delete item'
                                    });
                                }
                            }
                        });
                    }
                });
            });

            // --- Delete Barangay Stock ---
            $(document).on("click", ".deleteBarangayBtn", function () {
                const id = $(this).data('id');
                const row = $(`#barangay-row-${id}`);

                Swal.fire({
                    title: "Are you sure?",
                    text: "This will remove the item from barangay records forever.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#6b7280",
                    confirmButtonText: "Yes, delete it"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/barangay-stock/${id}`,
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: (res) => {
                                Toast.fire({ icon: 'success', title: res.message });
                                row.fadeOut(300, () => row.remove());
                            },
                            error: (xhr) => {
                                // If the error is the 422 we set in the controller
                                if (xhr.status === 422) {
                                    Swal.fire({
                                        title: "Action Denied",
                                        text: xhr.responseJSON.message,
                                        icon: "error"
                                    });
                                } else {
                                    showToastError(xhr.responseJSON?.message || 'Delete failed');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection