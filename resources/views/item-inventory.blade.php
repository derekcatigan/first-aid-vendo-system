@extends('layout.layout')

@section('content')
<section class="p-3 mx-3 md:mx-10 h-full">

    {{-- Header --}}
    <div class="bg-white border border-gray-300 rounded-sm p-3 flex flex-wrap items-center gap-3 mb-5">
        <div class="p-3 border border-gray-300 rounded-sm shadow">
            <i class="fa-solid fa-box-open text-2xl text-blue-700"></i>
        </div>
        <div>
            <h1 class="font-semibold text-lg">Inventory Management</h1>
            <h3 class="text-xs text-gray-500">Manage your vending machine inventory and barangay stocks</h3>
        </div>
    </div>

    {{-- Main layout: Barangay Stock + Vendo Inventory --}}
    <div class="flex flex-col lg:flex-row gap-4">

        {{-- --- Left Column: Barangay Stock Form --- --}}
        <div class="flex flex-col gap-4 flex-1">

            {{-- Add Barangay Stock Form --}}
            <div class="bg-white border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[300px]">
                <h3 class="font-bold mb-2">Add Barangay Stock</h3>
                <form id="barangayForm">
                    @csrf
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Item Name</legend>
                        <input type="text" name="itemName" class="input w-full rounded-xs" placeholder="e.g. Cotton Balls" required>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Description</legend>
                        <textarea name="itemDescription" class="textarea w-full rounded-xs" placeholder="Short description"></textarea>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Quantity</legend>
                        <input type="number" name="itemQuantity" class="input w-full rounded-xs" value="0" min="0" required>
                    </fieldset>
                    <fieldset class="mb-4">
                        <legend class="text-gray-500 text-xs">Low Stock Warning</legend>
                        <input type="number" name="itemLowStock" class="input w-full rounded-xs" value="0" min="0">
                    </fieldset>
                    <button type="submit" class="btn btn-success w-full">Add to Barangay Stock</button>
                </form>
            </div>
        </div>

        {{-- --- Right Column: Tables --- --}}
        <div class="flex flex-col gap-4 flex-1">

            {{-- Vending Machine Inventory Table --}}
            <div class="bg-white border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[400px]">
                <h3 class="font-bold mb-3">Current Vending Machine Items</h3>
                <div class="overflow-x-auto rounded-sm border border-gray-200 shadow-sm max-h-[260px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Keypad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Motor</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse ($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors text-sm {{ !$item->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->keypad }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->motor_index }}</td>
                                    <td class="px-4 py-3 text-center space-x-1">
                                        <button class="btn btn-xs btn-primary editBtn" data-id="{{ $item->id }}" data-quantity="{{ $item->quantity }}" {{ !$item->is_active ? 'disabled' : '' }}>Edit</button>
                                        <button class="btn btn-xs {{ $item->is_active ? 'btn-warning' : 'btn-success' }} toggleBtn" data-id="{{ $item->id }}">{{ $item->is_active ? 'Disable' : 'Enable' }}</button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-items-row">
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Barangay Stocks Table --}}
            <div class="bg-white border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[400px] max-h-[260px] overflow-y-auto">
                <h3 class="font-bold mb-3">Barangay Stocks</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="barangayTableBody" class="bg-white divide-y divide-gray-200">
                        @forelse ($barangayStocks as $bItem)
                            <tr class="text-sm">
                                <td class="px-4 py-3">{{ $bItem->item_name }}</td>
                                <td class="px-4 py-3" id="barangay-qty-{{ $bItem->id }}">{{ $bItem->quantity }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button class="btn btn-xs btn-success transferBtn" data-id="{{ $bItem->id }}">Transfer to Vendo</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="no-barangay-items">
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">No barangay items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>

{{-- Transfer Modal --}}
<input type="checkbox" id="transferModal" class="modal-toggle" />
<div class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Transfer to Vending Machine</h3>
        <form id="transferForm">
            @csrf
            <input type="hidden" id="transferBarangayId">
            <fieldset class="mb-3">
                <legend class="text-gray-500 text-xs">Quantity to Transfer</legend>
                <input type="number" id="transferQuantity" class="input w-full rounded-xs" min="1" required>
            </fieldset>
            <fieldset class="flex gap-3 mb-3">
                <div class="flex-1">
                    <legend class="text-gray-500 text-xs">Keypad</legend>
                    <input type="number" id="transferKeypad" class="input w-full rounded-xs" min="1" max="8" required>
                </div>
                <div class="flex-1">
                    <legend class="text-gray-500 text-xs">Motor</legend>
                    <input type="number" id="transferMotor" class="input w-full rounded-xs" min="1" max="8" required>
                </div>
            </fieldset>
            <div class="modal-action">
                <label for="transferModal" class="btn btn-ghost">Cancel</label>
                <button type="submit" class="btn btn-primary">Transfer</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Quantity Modal --}}
<input type="checkbox" id="editQtyModal" class="modal-toggle" />
<div class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Update Item Quantity</h3>
        <form id="editQtyForm">
            @csrf
            @method('PATCH')
            <input type="hidden" id="editItemId">
            <div class="mt-4">
                <label class="label">New Quantity</label>
                <input type="number" id="editQuantity" class="input input-bordered w-full" min="0" required>
            </div>
            <div class="modal-action">
                <label for="editQtyModal" class="btn btn-ghost">Cancel</label>
                <button type="submit" class="btn btn-primary">Save</button>
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
    $("#barangayForm").on("submit", function(e){
        e.preventDefault();
        const formData = $(this).serialize();
        $.post("{{ route('barangay.store') }}", formData, function(res){
            Toast.fire({icon:'success', title:res.message});
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
    $(document).on("click", ".transferBtn", function(){
        const id = $(this).data('id');
        $("#transferBarangayId").val(id);
        $("#transferModal").prop("checked", true);
    });

    // --- Submit Transfer ---
    $("#transferForm").on("submit", function(e){
        e.preventDefault();
        const id = $("#transferBarangayId").val();
        const data = {
            _token: "{{ csrf_token() }}",
            quantity: $("#transferQuantity").val(),
            keypad: $("#transferKeypad").val(),
            motor_index: $("#transferMotor").val(),
        };

        $.post(`/barangay-stock/transfer/${id}`, data, function(res){
            Toast.fire({icon:'success', title: res.message});
            $(`#barangay-qty-${id}`).text(res.remainingBarangayQty);

            const item = res.vendoItem;
            let existingRow = $(`#itemTableBody button[data-id="${item.id}"]`).closest("tr");
            if(existingRow.length){
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
        }).fail(function(xhr){
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

                if (res.is_active) {
                    btn.removeClass("btn-success").addClass("btn-warning").text("Disable");
                    editBtn.prop("disabled", false);
                    row.removeClass("opacity-50");
                } else {
                    btn.removeClass("btn-warning").addClass("btn-success").text("Enable");
                    editBtn.prop("disabled", true);
                    row.addClass("opacity-50");
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
