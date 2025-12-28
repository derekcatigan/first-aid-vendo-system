{{-- resources/views/item-inventory.blade.php --}}
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
                <h3 class="text-xs text-gray-500">Manage your vending machine inventory with precision</h3>
            </div>
        </div>

        {{-- Main layout: Form + Table --}}
        <div class="flex flex-col lg:flex-row gap-4">

            {{-- Add New Item Form --}}
            <div
                class="bg-white flex-1 border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[300px] max-w-full lg:max-w-[400px]">
                <h3 class="font-bold mb-2">Add New Item</h3>
                <h4 class="text-xs text-gray-500 mb-3">
                    Add product and initial stock. Keypad char and motor index must be unique.
                </h4>
                <form id="itemForm">
                    @csrf

                    {{-- Item Name --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Item Name</legend>
                        <input type="text" id="itemName" name="itemName" class="input w-full rounded-xs"
                            placeholder="e.g. Alcohol Wipes" required>
                    </fieldset>

                    {{-- Description --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Description</legend>
                        <textarea id="itemDescription" name="itemDescription" class="textarea w-full rounded-xs"
                            placeholder="Short description"></textarea>
                    </fieldset>

                    {{-- Initial Quantity --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Initial Quantity</legend>
                        <input type="number" id="itemQuantity" name="itemQuantity" class="input w-full rounded-xs" value="0"
                            min="0">
                    </fieldset>

                    {{-- Keypad & Motor --}}
                    <div class="flex flex-col sm:flex-row gap-3 mb-3">
                        <fieldset class="flex-1">
                            <legend class="text-gray-500 text-xs">Keypad Character</legend>
                            <input type="number" id="itemKey" name="itemKey" class="input w-full rounded-xs" value="0"
                                min="1" max="8" required>
                        </fieldset>
                        <fieldset class="flex-1">
                            <legend class="text-gray-500 text-xs">Motor</legend>
                            <input type="number" id="itemMotor" name="itemMotor" class="input w-full rounded-xs" value="1"
                                min="1" max="8" required>
                        </fieldset>
                    </div>

                    {{-- Low Stock Warning --}}
                    <fieldset class="mb-4">
                        <legend class="text-gray-500 text-xs">Low Stock Warning</legend>
                        <input type="number" id="itemLowStock" name="itemLowStock" class="input w-full rounded-xs" value="0"
                            min="0">
                    </fieldset>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn"
                        class="btn btn-primary w-full flex justify-center items-center gap-2">
                        <span id="btnText">Add Items</span>
                        <span id="btnSpinner" class="loading loading-dots loading-sm hidden"></span>
                    </button>
                </form>
            </div>

            {{-- Inventory Table --}}
            <div class="bg-white flex-1 border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[400px]">
                <h3 class="font-bold mb-3">Current Inventory Items</h3>
                <div class="overflow-x-auto rounded-sm border border-gray-200 shadow-sm max-h-[520px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Item Name</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Quantity</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Keypad</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Motor</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse ($items as $item)
                                <tr
                                    class="hover:bg-gray-50 transition-colors text-sm
                                                                                                                {{ !$item->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->item_name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->keypad }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->motor_index }}</td>
                                    {{-- <td class="px-4 py-3 text-center">
                                        <button class="btn btn-xs btn-primary editBtn" data-id="{{ $item->id }}"
                                            data-quantity="{{ $item->quantity }}">
                                            Edit
                                        </button>
                                        <button class="btn btn-xs btn-error deleteBtn" data-id="{{ $item->id }}">Delete</button>
                                    </td> --}}
                                    <td class="px-4 py-3 text-center space-x-1">
                                        <button class="btn btn-xs btn-primary editBtn" data-id="{{ $item->id }}"
                                            data-quantity="{{ $item->quantity }}" {{ !$item->is_active ? 'disabled' : '' }}>
                                            Edit
                                        </button>

                                        <button
                                            class="btn btn-xs {{ $item->is_active ? 'btn-warning' : 'btn-success' }} toggleBtn"
                                            data-id="{{ $item->id }}">
                                            {{ $item->is_active ? 'Disable' : 'Enable' }}
                                        </button>
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
        </div>
    </section>

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


    {{-- Pass used keypads and motors to JS --}}
    <script>
        let usedKeypads = @json(
            $items->where('is_active', true)->pluck('keypad')
        ).map(Number);

        let usedMotors = @json(
            $items->where('is_active', true)->pluck('motor_index')
        ).map(Number);
    </script>
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

            // Real-time Keypad validation
            $("#itemKey").on("input", function () {
                const val = parseInt($(this).val());
                if (val <= 0 || val > 8) { showToastError("Keypad must be between 1-8"); $(this).val(""); return; }
                if (usedKeypads.includes(val)) { showToastError("Keypad already assigned"); $(this).val(""); }
            });

            // Real-time Motor validation
            $("#itemMotor").on("input", function () {
                const val = parseInt($(this).val());
                if (val <= 0 || val > 8) { showToastError("Motor must be between 1-8"); $(this).val(""); return; }
                if (usedMotors.includes(val)) { showToastError("Motor already assigned"); $(this).val(""); }
            });

            // Open edit modal
            $(document).on("click", ".editBtn", function () {
                const itemId = $(this).data("id");
                const qty = $(this).data("quantity");

                $("#editItemId").val(itemId);
                $("#editQuantity").val(qty);

                $("#editQtyModal").prop("checked", true);
            });

            // Submit update quantity
            $("#editQtyForm").on("submit", function (e) {
                e.preventDefault();

                const itemId = $("#editItemId").val();
                const quantity = $("#editQuantity").val();

                $.ajax({
                    url: `/item/${itemId}/quantity`,
                    type: "PATCH",
                    data: {
                        _token: "{{ csrf_token() }}",
                        quantity: quantity
                    },
                    success: function (res) {
                        Toast.fire({ icon: "success", title: res.message });

                        // Update quantity in table
                        $(`button[data-id="${itemId}"]`)
                            .closest("tr")
                            .find("td:nth-child(2)")
                            .text(res.quantity);

                        // Update data attribute
                        $(`button[data-id="${itemId}"]`).data("quantity", res.quantity);

                        $("#editQtyModal").prop("checked", false);
                    },
                    error: function () {
                        Toast.fire({ icon: "error", title: "Failed to update quantity" });
                    }
                });
            });

            // AJAX Store
            $("#itemForm").on("submit", function (e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const btn = $("#submitBtn"), btnText = $("#btnText"), btnSpinner = $("#btnSpinner");

                btn.prop("disabled", true);
                btnText.addClass("hidden");
                btnSpinner.removeClass("hidden");

                $.ajax({
                    url: "{{ route('item.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        Toast.fire({ icon: "success", title: response.message });
                        const item = response.item;

                        $(".no-items-row").remove();

                        $("#itemTableBody").append(`
                                                                            <tr class="hover:bg-gray-50 transition-colors text-sm">
                                                                                <td class="px-4 py-3 text-gray-700 font-medium">${item.item_name}</td>
                                                                                <td class="px-4 py-3 text-gray-700">${item.quantity}</td>
                                                                                <td class="px-4 py-3 text-gray-700">${item.keypad}</td>
                                                                                <td class="px-4 py-3 text-gray-700">${item.motor_index}</td>
                                                                                <td class="px-4 py-3 text-center space-x-1">
                                    <button class="btn btn-xs btn-primary editBtn"
                                        data-id="${item.id}"
                                        data-quantity="${item.quantity}">
                                        Edit
                                    </button>

                                    <button class="btn btn-xs btn-warning toggleBtn"
                                        data-id="${item.id}">
                                        Disable
                                    </button>
                                </td>
                                                                            </tr>`);
                        $("#itemForm")[0].reset();
                        const usedKeypads = @json(
                            $items->where('is_active', true)->pluck('keypad')
                        ).map(Number);

                        const usedMotors = @json(
                            $items->where('is_active', true)->pluck('motor_index')
                        ).map(Number);

                    },
                    error: function (xhr) {
                        if (xhr.status === 422) Object.values(xhr.responseJSON.errors).forEach(msg => showToastError(msg[0]));
                        else showToastError("Something went wrong. Try again!");
                    },
                    complete: function () {
                        btn.prop("disabled", false);
                        btnText.removeClass("hidden");
                        btnSpinner.addClass("hidden");
                    }
                });
            });

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
                            btn
                                .removeClass("btn-success")
                                .addClass("btn-warning")
                                .text("Disable");

                            editBtn.prop("disabled", false);
                            row.removeClass("opacity-50");

                            usedKeypads.push(keypad);
                            usedMotors.push(motor);
                        } else {
                            btn
                                .removeClass("btn-warning")
                                .addClass("btn-success")
                                .text("Enable");

                            editBtn.prop("disabled", true);
                            row.addClass("opacity-50");

                            // Remove from used arrays
                            const kIndex = usedKeypads.indexOf(keypad);
                            if (kIndex !== -1) usedKeypads.splice(kIndex, 1);

                            const mIndex = usedMotors.indexOf(motor);
                            if (mIndex !== -1) usedMotors.splice(mIndex, 1);
                        }
                    },
                    error: function (xhr) {
                        Toast.fire({
                            icon: "error",
                            title: xhr.responseJSON?.message || "Action failed"
                        });
                    }
                });
            });


            // AJAX Delete
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
                                row.fadeOut(300, () => $(this).remove());
                            },
                            error: function () { Swal.fire({ icon: "error", title: "Error deleting item" }); }
                        });
                    }
                });
            });
        });
    </script>
@endsection