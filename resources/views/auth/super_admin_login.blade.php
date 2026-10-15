<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal &bull; DealSync CRM</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Inter+Tight:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --ds-primary: #4f46e5;
            --ds-primary-hover: #4338ca;
            --ds-primary-light: #eef2ff;
            --ds-primary-border: #c7d2fe;
            --ds-dark: #0f172a;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient background glow accents */
        body::before {
            content: '';
            position: absolute;
            top: -150px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -150px;
            right: 10%;
            width: 500px;
            height: 400px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, rgba(168, 85, 247, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.02);
            padding: 2.25rem;
            backdrop-filter: blur(12px);
        }

        .brand-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.35);
        }

        .portal-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #4338ca;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            margin-bottom: 0.6rem;
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .form-control {
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            padding: 0.7rem 0.95rem;
            font-size: 0.9rem;
            transition: all 0.15s ease-in-out;
            background-color: #f8fafc;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .input-group-text {
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #64748b;
        }

        .btn-super-login {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.625rem;
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.35);
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-super-login:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            color: #ffffff;
            box-shadow: 0 6px 20px 0 rgba(79, 70, 229, 0.45);
            transform: translateY(-1px);
        }

        .btn-super-login:active {
            transform: translateY(0);
        }

        .demo-credentials-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            margin-top: 1.5rem;
        }

        .badge-credential {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            color: #0f172a;
            font-size: 0.8rem;
        }

        .password-toggle-btn {
            border-top-right-radius: 0.625rem !important;
            border-bottom-right-radius: 0.625rem !important;
            border: 1px solid #cbd5e1;
            border-left: none;
            background-color: #f8fafc;
            color: #64748b;
            transition: all 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: #1e293b;
            background-color: #f1f5f9;
        }

        .footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Main Login Card -->
        <div class="auth-card">
            
            <!-- Brand & Heading -->
            <div class="text-center mb-4">
                <div class="brand-badge mb-3">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <span class="portal-pill">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Central Access
                    </span>
                </div>
                <h1 class="h4 fw-bold text-dark mb-1" style="font-family: 'Inter Tight', sans-serif;">
                    Super Admin Portal
                </h1>
                <p class="text-muted small mb-0">
                    Sign in with your master credentials to manage tenants and system operations.
                </p>
            </div>

            <!-- Flash Status / Success Alert -->
            @if(session('success') || session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 small border-0 mb-3" role="alert" style="background-color: #ecfdf5; color: #065f46; border-radius: 0.5rem;">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div>{{ session('success') ?? session('status') }}</div>
                </div>
            @endif

            <!-- Flash Error / Validation Error Alert -->
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small border-0 mb-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-radius: 0.5rem;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small border-0 mb-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-radius: 0.5rem;">
                    <div class="d-flex align-items-center gap-2 mb-1 fw-semibold">
                        <i class="bi bi-x-circle-fill"></i> Please resolve the following errors:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('super_admin.login.submit') }}" id="superAdminLoginForm" novalidate>
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="adminEmail" class="form-label">
                        <i class="bi bi-envelope me-1 text-primary"></i> Super Admin Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-at"></i>
                        </span>
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="adminEmail"
                            name="email"
                            value="{{ old('email', 'admin@dealsync.com') }}"
                            placeholder="admin@dealsync.com"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="adminPassword" class="form-label mb-0">
                            <i class="bi bi-key me-1 text-primary"></i> Master Password
                        </label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="adminPassword"
                            name="password"
                            placeholder="Enter password"
                            required
                        >
                        <button
                            class="btn password-toggle-btn"
                            type="button"
                            id="togglePasswordBtn"
                            onclick="togglePasswordVisibility()"
                            title="Show/Hide Password"
                        >
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Security Mode -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="rememberCheck"
                            {{ old('remember') ? 'checked' : '' }}
                            style="cursor: pointer;"
                        >
                        <label class="form-check-label small text-secondary" for="rememberCheck" style="cursor: pointer;">
                            Keep me signed in
                        </label>
                    </div>
                    <span class="badge bg-light text-secondary border small fw-normal py-1 px-2">
                        <i class="bi bi-lock-fill text-success me-1"></i> Central Guard
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-super-login" id="submitLoginBtn">
                    <span class="d-inline-flex align-items-center justify-content-center gap-2">
                        <span>Sign In to Super Admin</span>
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </button>
            </form>

            <!-- Seeder Credentials Quick-Fill helper -->
            <div class="demo-credentials-box">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold text-dark small d-flex align-items-center gap-1">
                        <i class="bi bi-database-check text-primary"></i>
                        Seeder Credentials (AdminSeeder)
                    </span>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary py-0 px-2"
                        style="font-size: 0.7rem; border-radius: 9999px;"
                        onclick="autoFillCredentials()"
                        title="Auto-fill credentials"
                    >
                        <i class="bi bi-magic me-1"></i> Auto Fill
                    </button>
                </div>
                <div class="d-flex flex-column gap-1 small text-muted">
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Email:</span>
                        <code class="badge-credential" id="demoEmail">admin@dealsync.com</code>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Password:</span>
                        <code class="badge-credential" id="demoPassword">admin123</code>
                    </div>
                </div>
            </div>

        </div>

        <!-- Security & Platform Footer -->
        <div class="footer-note">
            <p class="mb-1">
                &copy; {{ date('Y') }} DealSync CRM &bull; Multi-Tenant Database Architecture
            </p>
            <p class="text-secondary small mb-0">
                <i class="bi bi-shield-check text-success"></i> 256-bit Encrypted Central Authentication
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password Visibility Toggle
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('adminPassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Auto Fill Credentials from Seeder
        function autoFillCredentials() {
            const emailInput = document.getElementById('adminEmail');
            const passwordInput = document.getElementById('adminPassword');
            
            emailInput.value = 'admin@dealsync.com';
            passwordInput.value = 'admin123';

            // Flash visual feedback
            passwordInput.focus();
            
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Filled!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 1800);
        }

        // Form submit loading state
        document.getElementById('superAdminLoginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitLoginBtn');
            btn.disabled = true;
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                <span>Authenticating Super Admin...</span>
            `;
        });
    </script>
</body>
</html>
