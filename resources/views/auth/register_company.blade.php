<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Company Workspace &bull; DealSync CRM</title>
    
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
            --ds-accent: #4f46e5;
            --ds-dark: #0b0f19;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background: linear-gradient(135deg, #070a13 0%, #0e1424 50%, #0a0f1d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            position: relative;
            overflow-x: hidden;
            color: #f1f5f9;
        }

        /* Ambient Glowing Orbs */
        .glow-orb-1 {
            position: absolute;
            top: -100px;
            left: 20%;
            width: 600px;
            height: 450px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(99, 102, 241, 0) 70%);
            z-index: 0;
            pointer-events: none;
            filter: blur(50px);
        }

        .glow-orb-2 {
            position: absolute;
            bottom: -100px;
            right: 15%;
            width: 550px;
            height: 450px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.18) 0%, rgba(168, 85, 247, 0) 70%);
            z-index: 0;
            pointer-events: none;
            filter: blur(50px);
        }

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 820px;
        }

        .register-card {
            background: rgba(17, 24, 39, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.05);
            padding: 2.5rem 2.25rem;
        }

        .brand-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--ds-accent) 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 24px -5px rgba(79, 70, 229, 0.45);
        }

        .pill-badge {
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
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background-color: rgba(30, 41, 59, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #f8fafc;
            border-radius: 0.75rem;
            padding: 0.7rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(30, 41, 59, 0.95);
            border-color: var(--ds-accent);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.22);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .input-group-text {
            background-color: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            font-size: 0.85rem;
            border-radius: 0.75rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.825rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #c7d2fe;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .color-swatch {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .color-swatch:hover {
            transform: scale(1.15);
        }

        .color-swatch.active {
            border-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.6);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--ds-accent) 0%, #7c3aed 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 0.85rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            box-shadow: 0 10px 24px -5px rgba(79, 70, 229, 0.5);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px -5px rgba(79, 70, 229, 0.65);
            color: #ffffff;
        }

        .quick-demo-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 255, 255, 0.22);
            color: #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .quick-demo-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
        }

        .feature-ribbon {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.75rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.775rem;
            color: #cbd5e1;
        }

        .feature-item i {
            color: #10b981;
            font-size: 0.85rem;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 10, 19, 0.85);
            backdrop-filter: blur(8px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1rem;
        }
    </style>
</head>
<body>

    <div class="glow-orb-1"></div>
    <div class="glow-orb-2"></div>

    <!-- Spinner Loading Overlay when provisioning -->
    <div class="loading-overlay" id="provisionLoadingOverlay">
        <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="text-center">
            <h5 class="text-white fw-bold mb-1">Provisioning Company Workspace...</h5>
            <p class="text-secondary small mb-0">Creating isolated database, running 29 schema migrations &amp; setting up admin account.</p>
        </div>
    </div>

    <div class="register-container">
        <div class="register-card">

            <!-- Brand & Heading -->
            <div class="text-center mb-4">
                <div class="brand-badge mb-3">
                    <i class="bi bi-buildings-fill"></i>
                </div>
                <div>
                    <span class="pill-badge">
                        <i class="bi bi-stars"></i> Self-Service Organization Onboarding
                    </span>
                </div>
                <h1 class="h3 fw-bold text-white mb-1" style="font-family: 'Inter Tight', sans-serif;">
                    Register Your Company Workspace
                </h1>
                <p class="text-secondary small mb-0">
                    Get an isolated CRM database, enterprise RBAC security, and full administrative workspace in seconds.
                </p>
            </div>

            <!-- Features Banner -->
            <div class="feature-ribbon">
                <div class="row g-2">
                    <div class="col-sm-4 feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Isolated MySQL Database</span>
                    </div>
                    <div class="col-sm-4 feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Root Admin &amp; CEO Roles</span>
                    </div>
                    <div class="col-sm-4 feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Full CRM Schema &amp; Studio</span>
                    </div>
                </div>
            </div>

            <!-- Flash Error / Validation Alert -->
            @if($errors->any())
                <div class="alert alert-danger py-2.5 px-3 small border-0 mb-4" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border-radius: 0.75rem;">
                    <div class="d-flex align-items-center gap-2 mb-1 fw-semibold">
                        <i class="bi bi-x-circle-fill"></i> Please fix the following errors:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('tenant.register.submit') }}" id="registerCompanyForm" onsubmit="handleRegistrationSubmit(event)">
                @csrf
                <input type="hidden" name="accent_color" id="accentColorInput" value="#4F46E5">

                <div class="row g-4">
                    <!-- SECTION 1: Company Profile -->
                    <div class="col-lg-6">
                        <div class="section-header">
                            <i class="bi bi-building"></i>
                            <span>1. Company Profile</span>
                        </div>

                        <!-- Company Name -->
                        <div class="mb-3">
                            <label for="companyNameInput" class="form-label small text-secondary fw-medium mb-1">
                                Company / Organization Name *
                            </label>
                            <input
                                type="text"
                                name="company_name"
                                id="companyNameInput"
                                class="form-control"
                                placeholder="e.g. Retrotech Technologies"
                                value="{{ old('company_name') }}"
                                required
                                autofocus
                                oninput="syncDomainSlug(this.value)"
                            >
                        </div>

                        <!-- Workspace Domain / Subdomain -->
                        <div class="mb-3">
                            <label for="domainInput" class="form-label small text-secondary fw-medium mb-1">
                                Dedicated Workspace Domain / Slug *
                            </label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="domain"
                                    id="domainInput"
                                    class="form-control"
                                    placeholder="retrotech.local"
                                    value="{{ old('domain') }}"
                                    required
                                >
                            </div>
                            <div class="form-text text-secondary" style="font-size: 0.725rem;">
                                URL identifier for tenant isolation (e.g. <code>retrotech.local</code>).
                            </div>
                        </div>

                        <!-- Plan Tier & Max Seats -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-7">
                                <label for="planTierInput" class="form-label small text-secondary fw-medium mb-1">
                                    Subscription Tier
                                </label>
                                <select name="plan_tier" id="planTierInput" class="form-select">
                                    <option value="enterprise" selected>Enterprise ($39/mo)</option>
                                    <option value="professional">Professional ($29/mo)</option>
                                    <option value="starter">Starter ($19/mo)</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <label for="maxSeatsInput" class="form-label small text-secondary fw-medium mb-1">
                                    Included Seats
                                </label>
                                <input
                                    type="number"
                                    name="max_seats"
                                    id="maxSeatsInput"
                                    class="form-control"
                                    value="25"
                                    min="1"
                                    max="200"
                                >
                            </div>
                        </div>

                        <!-- Brand Accent Color Swatches -->
                        <div class="mb-3">
                            <label class="form-label small text-secondary fw-medium mb-1.5 d-block">
                                Brand Theme Color
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="color-swatch active" style="background-color: #4F46E5;" onclick="selectAccentColor('#4F46E5', this)"></span>
                                <span class="color-swatch" style="background-color: #059669;" onclick="selectAccentColor('#059669', this)"></span>
                                <span class="color-swatch" style="background-color: #7C3AED;" onclick="selectAccentColor('#7C3AED', this)"></span>
                                <span class="color-swatch" style="background-color: #0284C7;" onclick="selectAccentColor('#0284C7', this)"></span>
                                <span class="color-swatch" style="background-color: #D97706;" onclick="selectAccentColor('#D97706', this)"></span>
                                <span class="color-swatch" style="background-color: #E11D48;" onclick="selectAccentColor('#E11D48', this)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Master Administrator Account -->
                    <div class="col-lg-6">
                        <div class="section-header">
                            <i class="bi bi-person-badge"></i>
                            <span>2. Root Administrator Account</span>
                        </div>

                        <!-- Admin Full Name -->
                        <div class="mb-3">
                            <label for="adminNameInput" class="form-label small text-secondary fw-medium mb-1">
                                Administrator Full Name *
                            </label>
                            <input
                                type="text"
                                name="admin_name"
                                id="adminNameInput"
                                class="form-control"
                                placeholder="e.g. Retrotech Administrator"
                                value="{{ old('admin_name') }}"
                                required
                            >
                        </div>

                        <!-- Admin Work Email -->
                        <div class="mb-3">
                            <label for="adminEmailInput" class="form-label small text-secondary fw-medium mb-1">
                                Administrator Email (Login ID) *
                            </label>
                            <input
                                type="email"
                                name="admin_email"
                                id="adminEmailInput"
                                class="form-control"
                                placeholder="admin@retrotech.com"
                                value="{{ old('admin_email') }}"
                                required
                            >
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="adminPasswordInput" class="form-label small text-secondary fw-medium mb-1">
                                Master Password *
                            </label>
                            <div class="position-relative">
                                <input
                                    type="password"
                                    name="admin_password"
                                    id="adminPasswordInput"
                                    class="form-control pe-5"
                                    placeholder="••••••••••••"
                                    required
                                >
                                <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-secondary cursor-pointer" onclick="togglePasswordVisibility('adminPasswordInput', this)"></i>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="adminPasswordConfirmInput" class="form-label small text-secondary fw-medium mb-1">
                                Confirm Password *
                            </label>
                            <div class="position-relative">
                                <input
                                    type="password"
                                    name="admin_password_confirmation"
                                    id="adminPasswordConfirmInput"
                                    class="form-control pe-5"
                                    placeholder="••••••••••••"
                                    required
                                >
                                <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-secondary cursor-pointer" onclick="togglePasswordVisibility('adminPasswordConfirmInput', this)"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="pt-4 border-top border-secondary border-opacity-25 mt-4">
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                        <!-- Quick Auto-Fill Demo Button -->
                        <button type="button" class="quick-demo-btn d-flex align-items-center gap-2" onclick="fillRetrotechDemo()">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Auto-Fill "Retrotech" Demo</span>
                        </button>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-submit d-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" id="submitBtn">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                            <span>Register &amp; Launch Workspace</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Bottom Login Link -->
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                <span class="small text-secondary">
                    Already registered your organization?
                    <a href="{{ route('tenant.login') }}" class="text-decoration-none text-indigo-400 ms-1 fw-semibold hover-white">
                        Sign in to Workspace &rarr;
                    </a>
                </span>
            </div>

        </div>

        <!-- Footer -->
        <div class="text-center mt-3 text-secondary" style="font-size: 0.75rem;">
            &copy; {{ date('Y') }} DealSync Technologies &bull; Multi-Tenant CRM Engine
        </div>
    </div>

    <script>
        function syncDomainSlug(name) {
            if (!name) return;
            const slug = name.toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            const domainInput = document.getElementById('domainInput');
            if (domainInput && !domainInput.dataset.manuallyEdited) {
                domainInput.value = (slug || 'company') + '.local';
            }
        }

        document.getElementById('domainInput').addEventListener('input', function() {
            this.dataset.manuallyEdited = "true";
        });

        function selectAccentColor(color, el) {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('accentColorInput').value = color;
            document.documentElement.style.setProperty('--ds-accent', color);
        }

        function togglePasswordVisibility(fieldId, iconEl) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                iconEl.classList.remove('bi-eye');
                iconEl.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                iconEl.classList.remove('bi-eye-slash');
                iconEl.classList.add('bi-eye');
            }
        }

        function fillRetrotechDemo() {
            document.getElementById('companyNameInput').value = 'Retrotech';
            document.getElementById('domainInput').value = 'retrotech.local';
            document.getElementById('adminNameInput').value = 'Retrotech Administrator';
            document.getElementById('adminEmailInput').value = 'admin@retrotech.com';
            document.getElementById('adminPasswordInput').value = 'Retrotech123!';
            document.getElementById('adminPasswordConfirmInput').value = 'Retrotech123!';
            document.getElementById('maxSeatsInput').value = '25';
        }

        function handleRegistrationSubmit(e) {
            // Show loading animation because isolated MySQL creation & 29 migrations take a few seconds
            document.getElementById('provisionLoadingOverlay').style.display = 'flex';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Provisioning Workspace...';
        }
    </script>
</body>
</html>
