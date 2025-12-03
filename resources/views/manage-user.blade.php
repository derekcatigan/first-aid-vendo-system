{{-- resources/views/manage-user.blade.php --}}
@extends('layout.layout')

@section('content')
    <section class="p-3 mx-3 md:mx-10 h-full">

        {{-- Header --}}
        <div class="bg-white border border-gray-300 rounded-sm p-3 flex flex-wrap items-center gap-3 mb-5">
            <div class="p-3 border border-gray-300 rounded-sm shadow">
                <i class="fa-solid fa-users text-2xl text-blue-700"></i>
            </div>
            <div>
                <h1 class="font-semibold text-lg">User Management</h1>
                <h3 class="text-xs text-gray-500">Create and manage administrative and worker accounts</h3>
            </div>
        </div>

        {{-- Main Layout --}}
        <div class="flex flex-col lg:flex-row gap-4">

            {{-- Create User Form --}}
            <div
                class="bg-white flex-1 border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[300px] max-w-full lg:max-w-[400px]">
                <h3 class="font-bold mb-4">Create New User</h3>
                <form id="userCreateForm">
                    @csrf

                    {{-- Full Name --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Full Name</legend>
                        <input type="text" id="fullName" name="fullName" class="input w-full rounded-xs"
                            placeholder="Enter full name" required>
                    </fieldset>

                    {{-- Email --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Email</legend>
                        <input type="email" id="email" name="email" class="input w-full rounded-xs"
                            placeholder="Enter email address" required>
                    </fieldset>

                    {{-- Role --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Role</legend>
                        <select id="role" name="role" class="select w-full">
                            <option value="admin" selected>Admin</option>
                            <option value="worker">Worker</option>
                        </select>
                    </fieldset>

                    {{-- Password --}}
                    <fieldset class="mb-3">
                        <legend class="text-gray-500 text-xs">Password</legend>
                        <input type="password" id="password" name="password" class="input w-full rounded-xs"
                            placeholder="Enter password" required>
                    </fieldset>

                    {{-- Confirm Password --}}
                    <fieldset class="mb-4">
                        <legend class="text-gray-500 text-xs">Confirm Password</legend>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="input w-full rounded-xs" placeholder="Re-enter password" required>
                    </fieldset>

                    <button type="submit" id="submitBtn"
                        class="btn btn-primary w-full flex justify-center items-center gap-2">
                        <span id="btnText">Create Account</span>
                        <span id="btnSpinner" class="loading loading-dots loading-sm hidden"></span>
                    </button>
                </form>
            </div>

            {{-- Users Table --}}
            <div class="bg-white flex-1 border border-gray-300 p-4 rounded-sm shadow min-w-full lg:min-w-[400px]">
                <h3 class="font-bold mb-3">Current Users</h3>

                <div class="overflow-x-auto rounded-sm border border-gray-200 shadow-sm max-h-[520px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Full Name</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Email</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Role</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr class="text-sm hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-gray-700 capitalize">{{ $user->role }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button class="btn btn-xs btn-error deleteBtn" data-id="{{ $user->id }}">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </section>
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

            const showToastError = (message) => Toast.fire({ icon: "error", title: message });

            // Create User AJAX
            $("#userCreateForm").on("submit", function (e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const btn = $("#submitBtn");
                const btnText = $("#btnText");
                const btnSpinner = $("#btnSpinner");

                btn.prop("disabled", true);
                btnText.addClass("hidden");
                btnSpinner.removeClass("hidden");

                $.ajax({
                    url: "{{ route('user.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        Toast.fire({ icon: "success", title: response.message });
                        setTimeout(() => location.reload(), 500);
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            Object.values(xhr.responseJSON.errors).forEach(err => showToastError(err[0]));
                        } else {
                            showToastError("Something went wrong. Try again!");
                        }
                    },
                    complete: function () {
                        btn.prop("disabled", false);
                        btnText.removeClass("hidden");
                        btnSpinner.addClass("hidden");
                    }
                });
            });

            // Delete User AJAX
            $(document).on("click", ".deleteBtn", function () {
                const userId = $(this).data("id");
                const row = $(this).closest("tr");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This user will be permanently removed.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/user/" + userId,
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                Swal.fire({
                                    icon: "success",
                                    title: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                row.fadeOut(300, () => $(this).remove());
                                setTimeout(() => location.reload(), 500);
                            },
                            error: function () {
                                Swal.fire({ icon: "error", title: "Failed to delete user" });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection