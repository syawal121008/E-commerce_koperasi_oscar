<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Koperasi SMKIUTAMA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
                background-size: 400% 400%;
                animation: gradientShift 15s ease infinite;
                position: relative;
                overflow: hidden;
            }
            
            .gradient-bg::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
                animation: float 20s ease-in-out infinite;
            }
            
            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            
            .glass-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 35px 60px rgba(0, 0, 0, 0.15);
            }
            
            .floating-icons {
                position: absolute;
                color: rgba(255, 255, 255, 0.1);
                font-size: 2rem;
                animation: float 6s ease-in-out infinite;
                pointer-events: none;
            }
            
            .icon-1 { top: 10%; left: 10%; animation-delay: -2s; }
            .icon-2 { top: 20%; right: 15%; animation-delay: -4s; }
            .icon-3 { bottom: 20%; left: 15%; animation-delay: -1s; }
            .icon-4 { bottom: 15%; right: 10%; animation-delay: -3s; }
            .icon-5 { top: 50%; left: 5%; animation-delay: -5s; }
            .icon-6 { top: 60%; right: 5%; animation-delay: -0.5s; }
            
            .brand-logo {
                background: linear-gradient(135deg, #667eea, #764ba2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                font-weight: 700;
                font-size: 2.5rem;
                text-align: center;
                margin-bottom: 0.5rem;
                animation: pulse 2s ease-in-out infinite;
            }
            
            .input-group {
                position: relative;
                margin-bottom: 1.5rem;
            }
            
            .input-with-icon {
                padding-left: 3rem;
                padding-right: 3rem;
                border: 2px solid #e5e7eb;
                border-radius: 0.75rem;
                transition: all 0.3s ease;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
            }
            
            .input-with-icon:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                transform: translateY(-2px);
                background: rgba(255, 255, 255, 1);
            }
            
            .input-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #9ca3af;
                transition: color 0.3s ease;
                z-index: 10;
            }
            
            .toggle-password {
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #9ca3af;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 10;
            }
            
            .toggle-password:hover {
                color: #667eea;
                transform: translateY(-50%) scale(1.1);
            }
            
            .input-with-icon:focus + .input-icon,
            .input-with-icon:focus ~ .input-icon {
                color: #667eea;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea, #764ba2);
                border: none;
                color: white;
                font-weight: 600;
                padding: 0.75rem 2rem;
                border-radius: 0.75rem;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .btn-primary::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.5s;
            }
            
            .btn-primary:hover::before {
                left: 100%;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            }
            
            .social-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                border: 2px solid #e5e7eb;
                background: rgba(255, 255, 255, 0.9);
                color: #6b7280;
                transition: all 0.3s ease;
                margin: 0 0.5rem;
            }
            
            .social-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
            
            .social-btn.facebook:hover {
                background: #1877f2;
                color: white;
                border-color: #1877f2;
            }
            
            .social-btn.google:hover {
                background: #ea4335;
                color: white;
                border-color: #ea4335;
            }
            
            .social-btn.twitter:hover {
                background: #1da1f2;
                color: white;
                border-color: #1da1f2;
            }
            
            /* Custom SweetAlert2 styles */
            .swal2-popup {
                border-radius: 1rem;
                backdrop-filter: blur(10px);
            }
            
            .swal2-success .swal2-success-ring {
                border-color: #667eea;
            }
            
            .swal2-success .swal2-success-fix,
            .swal2-success .swal2-success-line {
                background-color: #667eea;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen gradient-bg flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative">
            
            <!-- Floating E-commerce Icons -->
            <div class="floating-icons icon-1"><i class="fas fa-shopping-cart"></i></div>
            <div class="floating-icons icon-2"><i class="fas fa-tags"></i></div>
            <div class="floating-icons icon-3"><i class="fas fa-gift"></i></div>
            <div class="floating-icons icon-4"><i class="fas fa-credit-card"></i></div>
            <div class="floating-icons icon-5"><i class="fas fa-truck"></i></div>
            <div class="floating-icons icon-6"><i class="fas fa-star"></i></div>
            
            <!-- Logo -->
            <div class="mb-6">
                <div class="brand-logo">
                    <i class="fas fa-store mr-2"></i>Koperasi SMKIUTAMA
                </div>
                <p class="text-white/80 text-center text-sm">Belanja Online Terpercaya</p>
            </div>

            <!-- Card Container -->
            <div class="w-full sm:max-w-md glass-card rounded-2xl px-8 py-8">
                {{ $slot }}
            </div>
        </div>

        <!-- JavaScript untuk Show/Hide Password dan SweetAlert -->
        <script>
            // Toggle Password Visibility
            function togglePassword(inputId) {
                const input = document.getElementById(inputId);
                const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"]`);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    icon.innerHTML = '<i class="fas fa-eye"></i>';
                }
            }

            // SweetAlert untuk Error Messages
            @if ($errors->any())
                document.addEventListener('DOMContentLoaded', function() {
                    let errorMessages = '';
                    @foreach ($errors->all() as $error)
                        errorMessages += '{{ $error }}<br>';
                    @endforeach
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: errorMessages,
                        confirmButtonColor: '#667eea',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: 'rgba(0, 0, 0, 0.4)',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                });
            @endif

            // SweetAlert untuk Success Messages
            @if (session('status'))
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('status') }}',
                        confirmButtonColor: '#667eea',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: 'rgba(0, 0, 0, 0.4)',
                        timer: 3000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                });
            @endif

            // Animate inputs on focus
            document.addEventListener('DOMContentLoaded', function() {
                const inputs = document.querySelectorAll('.input-with-icon');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('focused');
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement.classList.remove('focused');
                    });
                });
            });
        </script>
    </body>
</html>