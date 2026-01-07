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
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Add Barangay Stock
                </h3>

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

                    <button class="btn btn-primary w-full">
                        Add Stock
                    </button>
                </form>
            </div>

            {{-- ========== VENDING MACHINE INVENTORY ========= --}}
            <div class="lg:col-span-2 bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Vending Machine Inventory
                </h3>

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
                                        {{-- Edit --}}
                                        <button
                                            class="btn btn-xs btn-outline btn-primary editBtn"
                                            data-id="{{ $item->id }}"
                                            data-quantity="{{ $item->quantity }}"
                                            {{ !$item->is_active ? 'disabled' : '' }}>
                                            Edit
                                        </button>

                                        {{-- Toggle --}}
                                        <button
                                            class="toggleBtn btn btn-xs
                                                {{ $item->is_active
                                                    ? 'btn-outline btn-warning'
                                                    : 'btn-success animate-pulse shadow-md'
                                                }}"
                                            data-id="{{ $item->id }}">
                                            @if($item->is_active)
                                                <i class="fa-solid fa-ban mr-1"></i> Disable
                                            @else
                                                <i class="fa-solid fa-play mr-1"></i> Enable
                                            @endif
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-items-row">
                                    <td colspan="5" class="text-center text-gray-400 py-6">
                                        No items found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ========== BARANGAY STOCKS ========= --}}
            <div class="lg:col-span-3 bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Barangay Stocks
                </h3>

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
                                <tr>
                                    <td class="font-medium">{{ $bItem->item_name }}</td>
                                    <td id="barangay-qty-{{ $bItem->id }}">
                                        {{ $bItem->quantity }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-success transferBtn" data-id="{{ $bItem->id }}">
                                            Transfer
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-barangay-items">
                                    <td colspan="3" class="text-center text-gray-400 py-6">
                                        No barangay items
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    {{-- ================= TRANSFER MODAL ================= --}}
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
                        <label class="text-xs text-gray-500">Keypad</label>
                        <input type="number" id="transferKeypad" class="input input-bordered w-full" min="1" max="8"
                            required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Motor</label>
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

    {{-- ================= EDIT QUANTITY MODAL ================= --}}
    <input type="checkbox" id="editQtyModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-3">Update Item Quantity</h3>

            <form id="editQtyForm" class="space-y-3">
                @csrf
                @method('PATCH')

                <input type="hidden" id="editItemId">

                <div>
                    <label class="text-xs text-gray-500">New Quantity</label>
                    <input type="number" id="editQuantity" class="input input-bordered w-full" min="0" required>
                </div>

                <div class="modal-action">
                    <label for="editQtyModal" class="btn btn-ghost">Cancel</label>
                    <button class="btn btn-primary">Save</button>
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

            // --- Barangay Stock Form ---
            $("#barangayForm").on("submit", function (e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.post("{{ route('barangay.store') }}", formData, function (res) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $(".no-barangay-items").remove();
                    $("#barangayTableBody").append(`
                                    <tr class="text-sm">
                                        <td class="px-4 py-3">${res.barangayItem.item_name}</td>
                                        <td class="px-4 py-3" id="barangay-qty-${res.barangayItem.id}">${res.barangayItem.quantity}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button class="btn btn-xs btn-success transferBtn" data-id="${res.barangayItem.id}">Transfer to Vendo</button>
                                        </td>
                                    </tr>
                                `);
                    $("#barangayForm")[0].reset();
                }).fail(() => showToastError('Failed to add barangay stock'));
            });

            // --- Open Transfer Modal ---
            $(document).on("click", ".transferBtn", function () {
                const id = $(this).data('id');
                $("#transferBarangayId").val(id);
                $("#transferModal").prop("checked", true);
            });

            // --- Submit Transfer ---
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

                    const item = res.vendoItem;
                    let existingRow = $(`#itemTableBody button[data-id="${item.id}"]`).closest("tr");
                    if (existingRow.length) {
                        existingRow.find("td:nth-child(2)").text(item.quantity);
                    } else {
                        $(".no-items-row").remove();
                        $("#itemTableBody").append(`
                                        <tr class="hover:bg-gray-50 transition-colors text-sm">
                                            <td class="px-4 py-3 text-gray-700 font-medium">${item.item_name}</td>
                                            <td class="px-4 py-3 text-gray-700">${item.quantity}</td>
                                            <td class="px-4 py-3 text-gray-700">${item.keypad}</td>
                                            <td class="px-4 py-3 text-gray-700">${item.motor_index}</td>
                                            <td class="px-4 py-3 text-center space-x-1">
                                                <button class="btn btn-xs btn-primary editBtn" data-id="${item.id}" data-quantity="${item.quantity}">Edit</button>
                                                <button class="btn btn-xs btn-warning toggleBtn" data-id="${item.id}">Disable</button>
                                            </td>
                                        </tr>
                                    `);
                    }

                    $("#transferModal").prop("checked", false);
                    $("#transferForm")[0].reset();
                }).fail(function (xhr) {
                    showToastError(xhr.responseJSON?.message || 'Transfer failed');
                });
            });

            // --- Edit Quantity ---
            $(document).on("click", ".editBtn", function () {
                const itemId = $(this).data("id");
                const qty = $(this).data("quantity");

                $("#editItemId").val(itemId);
                $("#editQuantity").val(qty);
                $("#editQtyModal").prop("checked", true);
            });

            $("#editQtyForm").on("submit", function (e) {
                e.preventDefault();
                const itemId = $("#editItemId").val();
                const quantity = $("#editQuantity").val();

                $.ajax({
                    url: `/item/${itemId}/quantity`,
                    type: "PATCH",
                    data: { _token: "{{ csrf_token() }}", quantity },
                    success: function (res) {
                        Toast.fire({ icon: "success", title: res.message });
                        $(`button[data-id="${itemId}"]`).closest("tr").find("td:nth-child(2)").text(res.quantity);
                        $(`button[data-id="${itemId}"]`).data("quantity", res.quantity);
                        $("#editQtyModal").prop("checked", false);
                    },
                    error: function () {
                        Toast.fire({ icon: "error", title: "Failed to update quantity" });
                    }
                });
            });

            // --- Toggle Enable/Disable ---
            $(document).on("click", ".toggleBtn", function () {
                const btn = $(this);
                const itemId = btn.data("id");

                $.ajax({
                    url: `/item/${itemId}/toggle-status`,
                    type: "PATCH",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        Toast.fire({ icon: "success", title: res.message });

                        const row = btn.closest("tr");
                        const editBtn = row.find(".editBtn");
                        const keypad = parseInt(row.find("td:nth-child(3)").text());
                        const motor = parseInt(row.find("td:nth-child(4)").text());
                        const dataCells = row.find(".data-cell");

                        if (res.is_active) {
                            btn
                                .removeClass("btn-success animate-pulse shadow-md")
                                .addClass("btn-outline btn-warning")
                                .html('<i class="fa-solid fa-ban mr-1"></i> Disable');

                            editBtn.prop("disabled", false);
                            dataCells.removeClass("opacity-40");
                        } else {
                            btn
                                .removeClass("btn-outline btn-warning")
                                .addClass("btn-success animate-pulse shadow-md")
                                .html('<i class="fa-solid fa-play mr-1"></i> Enable');

                            editBtn.prop("disabled", true);
                            dataCells.addClass("opacity-40");
                        }
                    },
                    error: function (xhr) {
                        Toast.fire({ icon: "error", title: xhr.responseJSON?.message || "Action failed" });
                    }
                });
            });

            // --- Delete Item ---
            $(document).on("click", ".deleteBtn", function () {
                const itemId = $(this).data("id");
                const row = $(this).closest("tr");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This item will be permanently removed.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/item/" + itemId,
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                Swal.fire({ icon: "success", title: response.message, timer: 2000, showConfirmButton: false });
                                row.fadeOut(300, () => row.remove());
                            },
                            error: function () { Swal.fire({ icon: "error", title: "Error deleting item" }); }
                        });
                    }
                });
            });
        });
    </script>
@endsection