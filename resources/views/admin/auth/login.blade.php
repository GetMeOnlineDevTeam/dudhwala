<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap core + Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">

            <div class="text-center mb-3">
              <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo" style="height:72px;">
              <h5 class="mt-3 mb-0">Admin Login</h5>
            </div>

            @if (session('status'))
              <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" novalidate>
              @csrf

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  id="email"
                  type="email"
                  name="email"
                  value="{{ old('email') }}"
                  class="form-control @error('email') is-invalid @enderror"
                  placeholder="admin@example.com"
                  required
                  autofocus
                >
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input
                    id="password"
                    type="password"                      
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                  >
                  <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                  </button>
                  @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                  <label class="form-check-label" for="remember">Remember me</label>
                </div>
              </div>

              <button type="submit" class="btn btn-success w-100">Login</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Minimal JS: Bootstrap (optional) + tiny toggle script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      const pwd = document.getElementById('password');
      const btn = document.getElementById('togglePassword');
      const icon = btn.querySelector('i');

      btn.addEventListener('click', function () {
        const isPwd = pwd.getAttribute('type') === 'password';
        pwd.setAttribute('type', isPwd ? 'text' : 'password');
        icon.classList.toggle('bi-eye', !isPwd);
        icon.classList.toggle('bi-eye-slash', isPwd);
        btn.setAttribute('aria-label', isPwd ? 'Hide password' : 'Show password');
      });
    })();
  </script>
</body>
</html>
