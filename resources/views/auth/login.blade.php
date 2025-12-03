{{-- resources\views\auth\login.blade.php --}}
@extends('layout.app')

@section('content')
    <main class="h-screen bg-base-100/10 flex justify-center items-center">
        <section class="bg-white border border-gray-300 rounded-sm shadow p-3 w-[800px]">
            <div class="flex flex-wrap items-stretch h-full">

                {{-- Login Form Section --}}
                <div class="flex-1 min-w-[200px]">

                    {{-- Login Title --}}
                    <div class="flex items-center gap-3">
                        <div class="p-1 border border-gray-300 rounded shadow-lg flex justify-center items-center">
                            <img src="{{ asset('assets/images/first-aid.png') }}" alt="Logo" class="w-14 h-14 object-cover">
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold">First Aid System</h1>
                            <h3 class="text-sm text-gray-500">Please sign in to access your account</h3>
                        </div>
                    </div>

                    {{-- Login Form --}}
                    <form class="p-2">
                        @csrf

                        {{-- Email --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Email</legend>
                            <input type="email" id="email" name="email" class="input w-full"
                                placeholder="e.g. johndoe@email.com" />
                        </fieldset>

                        {{-- Password --}}
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Password</legend>
                            <input type="password" id="password" name="password" class="input w-full"
                                placeholder="password" />
                        </fieldset>

                        {{-- Toggle Show Password --}}
                        <div class="mt-2 flex items-center gap-2">
                            <input type="checkbox" id="showPassword" class="checkbox" />
                            <label for="showPassword" class="text-sm">Show Password</label>
                        </div>

                        <div class="w-full mt-5">
                            <button type="submit" id="loginBtn" class="btn btn-block btn-primary text-white">
                                <span id="btnText">Login</span>
                                <span id="btnSpinner" class="loading loading-dots loading-sm hidden"></span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Login Info Section --}}
                <div class="flex-1 min-w-[200px]">
                    <div class="h-full flex flex-col justify-center items-center">

                        <div
                            class="p-1 border border-gray-300 rounded shadow-lg flex justify-center items-center overflow-hidden">
                            <img src="{{ asset('assets/images/logo-con.png') }}" alt="Logo" class="w-24 h-24 object-cover">
                        </div>

                        <div class="mt-3 text-center space-y-1">
                            <h4 class="text-lg font-bold">Your Partner in Emergency Response</h4>
                            <h3 class="text-sm text-gray-500">Our system provides critical information and tools to help you
                                respond effectively during emergencies. Log in to access your dashboard and resources.</h3>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@section('script')
    <script>
        $(document).ready(function () {

            // Show/Hide Password
            $('#showPassword').on('change', function () {
                const passwordField = $('#password');
                const type = $(this).is(':checked') ? 'text' : 'password';
                passwordField.attr('type', type);
            });

            // AJAX Login
            $('form').on('submit', function (e) {
                e.preventDefault();

                let email = $('#email').val();
                let password = $('#password').val();
                let formData = $(this).serialize();

                let $loginBtn = $('#loginBtn');
                let $btnText = $('#btnText');
                let $btnSpinner = $('#btnSpinner');

                // Basic validation before disabling button
                if (!email || !password) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing fields',
                        text: 'Please enter your email and password.',
                        toast: true,
                        position: 'top-right',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    return;
                }

                // Now disable the button + show spinner
                $loginBtn.prop('disabled', true);
                $btnText.addClass('hidden');
                $btnSpinner.removeClass('hidden');

                $.ajax({
                    url: "{{ route('login.post') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-right',
                            showConfirmButton: false,
                            timer: 2000,
                        });

                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    },
                    error: function (xhr) {
                        $loginBtn.prop('disabled', false);
                        $btnText.removeClass('hidden');
                        $btnSpinner.addClass('hidden');

                        if (xhr.status === 422) {
                            Swal.fire({
                                icon: 'error',
                                title: xhr.responseJSON.message,
                                toast: true,
                                position: 'top-right',
                                showConfirmButton: false,
                                timer: 2500,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: 'Something went wrong. Please try again later.',
                                toast: true,
                                position: 'top-right',
                                showConfirmButton: false,
                                timer: 2500,
                            });
                        }
                    }
                });

            });
        });
    </script>
@endsection