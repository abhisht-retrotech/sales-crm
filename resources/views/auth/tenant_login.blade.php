<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name ?? 'Organization' }} &bull; Sign In &bull; DealSync CRM</title>
    
    <!-- Google Fonts: Inter & Inter Tight -->
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
            --ds-accent: {{ $tenant->accent_color ?? '#4f46e5' }};
            --ds-dark: #0f172a;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background: linear-gradient(135deg, #0b0f19 0%, #111827 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
            color: #f1f5f9;
        }

        /* Ambient Glowing Orbs */
        .glow-orb-1 {
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(99, 102, 241, 0) 70%);
            z-index: 0;
            pointer-events: none;
            filter: blur(40px);
        }

        .glow-orb-2 {
            position: absolute;
            bottom: -10%;
            right: 15%;
            width: 500px;
            height: 400px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.18) 0%, rgba(168, 85, 247, 0) 70%);
            z-index: 0;
            pointer-events: none;
            filter: blur(40px);
        }

        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
            padding: 2.5rem 2rem;
        }

        .brand-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--ds-accent) 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            box-shadow: 0 12px 24px -6px rgba(79, 70, 229, 0.45);
        }

        .org-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #a5b4fc;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }

        .form-control {
            background-color: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.925rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus {
            background-color: rgba(30, 41, 59, 0.95);
            border-color: var(--ds-accent);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--ds-accent) 0%, #6366f1 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 0.8rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -6px rgba(79, 70, 229, 0.55);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .quick-demo-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 255, 255, 0.2);
            color: #94a3b8;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            font-size: 0.8rem;
            width: 100%;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .quick-demo-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.35);
            color: #f1f5f9;
        }

        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="glow-orb-1"></div>
    <div class="glow-orb-2"></div>

    <div class="auth-container">
        <!-- Main Card -->
        <div class="auth-card">
            
            <!-- Header & Branding -->
            <div class="text-center mb-4">
                <div class="brand-icon-box mb-3">
                    @if(!empty($tenant->logo_url))
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" style="max-width: 36px; max-height: 36px; object-fit: contain;">
                    @else
                        <i class="bi bi-buildings-fill"></i>
                    @endif
                </div>

                <div>
                    <span class="org-badge">
                        <i class="bi bi-shield-check"></i> {{ $tenant->name ?? 'DealSync Organization' }}
                    </span>
                    <div class="small text-secondary mb-2" style="font-size: 0.75rem; letter-spacing: 0.02em;">
                        <i class="bi bi-globe2 me-1 text-primary"></i> <span class="text-white fw-semibold">{{ request()->getHost() }}</span> &bull; <code>{{ $tenant->domain ?? 'isolated' }}</code>
                    </div>
                </div>

                <h1 class="h4 fw-bold text-white mb-1" style="font-family: 'Inter Tight', sans-serif;">
                    Sign in to Workspace
                </h1>
                <p class="text-secondary small mb-0">
                    Enter your organization credentials to access your CRM.
                </p>
            </div>

            <!-- Flash Status Messages -->
            @if(session('success') || session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 small border-0 mb-3" style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border-radius: 0.65rem;">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div>{{ session('success') ?? session('status') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small border-0 mb-3" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border-radius: 0.65rem;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small border-0 mb-3" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border-radius: 0.65rem;">
                    <div class="d-flex align-items-center gap-2 mb-1 fw-semibold">
                        <i class="bi bi-x-circle-fill"></i> Authentication failed:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Sign In Form -->
            <form method="POST" action="{{ route('tenant.login.submit') }}" id="tenantLoginForm">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="tenantEmail" class="form-label small text-secondary fw-medium mb-1">
                        Work Email Address
                    </label>
                    <div class="position-relative">
                        <input
                            type="email"
                            name="email"
                            id="tenantEmail"
                            class="form-control ps-3"
                            placeholder="name@company.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="tenantPassword" class="form-label small text-secondary fw-medium mb-0">
                            Password
                        </label>
                    </div>
                    <div class="position-relative">
                        <input
                            type="password"
                            name="password"
                            id="tenantPassword"
                            class="form-control ps-3 pe-5"
                            placeholder="••••••••••••"
                            required
                        >
                        <i class="bi bi-eye password-toggle" id="togglePasswordBtn" onclick="togglePasswordVisibility()"></i>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" style="background-color: rgba(30, 41, 59, 0.8); border-color: rgba(255,255,255,0.2);">
                        <label class="form-check-label small text-secondary" for="rememberMe">
                            Keep me signed in
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-submit w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Sign In to CRM</span>
                </button>

                <!-- Quick Admin Demo Fill -->
                <button type="button" class="quick-demo-btn d-flex align-items-center justify-content-center gap-2" onclick="fillAdminCredentials()">
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                    <span>Auto-Fill Tenant Admin Demo</span>
                </button>
            </form>

            <!-- Bottom Divider & Links -->
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                <div class="mb-2">
                    <span class="small text-secondary">Need a new workspace for your team?</span>
                    <a href="{{ route('tenant.register') }}" class="text-decoration-none small text-indigo-400 fw-semibold ms-1 hover-white">
                        Register Company &rarr;
                    </a>
                </div>
                <div>
                    <a href="{{ route('super_admin.login') }}" class="text-decoration-none small text-secondary hover-white transition">
                        <i class="bi bi-shield-lock me-1"></i> Switch to Super Admin Portal &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- Footer Note -->
        <div class="text-center mt-3 text-secondary" style="font-size: 0.75rem;">
            &copy; {{ date('Y') }} DealSync Technologies &bull; Multi-Tenant CRM Engine
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('tenantPassword');
            const toggleIcon = document.getElementById('togglePasswordBtn');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        function fillAdminCredentials() {
            @if(!empty($tenantAdminEmail))
                document.getElementById('tenantEmail').value = '{{ $tenantAdminEmail }}';
                document.getElementById('tenantPassword').value = '{{ str_contains(strtolower($tenant->name ?? ""), "retrotech") ? "Retrotech123!" : "admin123" }}';
            @else
                document.getElementById('tenantEmail').value = 'admin@dealsync.com';
                document.getElementById('tenantPassword').value = 'admin123';
            @endif
        }
    </script>
</body>
</html>
