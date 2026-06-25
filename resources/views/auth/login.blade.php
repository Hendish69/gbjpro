<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GBJ PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .gradient-custom {
        /* fallback for old browsers */
        background: #6a11cb;

        /* Chrome 10-25, Safari 5.1-6 */
        background: -webkit-linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1));

        /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        background: linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1))
        }
    </style>
</head>
<body>
<section class="vh-100 gradient-custom">
     @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card bg-dark text-white" style="border-radius: 1rem;">
            <form method="POST" action="{{ route('login') }}">
            @csrf
                <div class="card-body p-5 text-center">
                    <div class="text-center">
                    <img src="{{ asset('images/GbjPro.png') }}" 
                        style="width: 185px;" alt="logo">
                    <h4 class="mt-1 mb-5 pb-1">PTM GBJ PRO</h4>
                    </div>
                    <p>Please login to your account</p>
                    <div class="mb-md-5 mt-md-4 pb-5">
                        <div data-mdb-input-init class="form-outline form-white mb-4">
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            <label class="form-label" for="email">Email</label>
                        </div>

                        <div data-mdb-input-init class="form-outline form-white mb-4">
                           <input type="password" class="form-control" id="password" name="password" required>
                            <label class="form-label" for="typePasswordX">Password</label>
                        </div>

                        <p class="small mb-5 pb-lg-2"><a class="text-white-50" href="#!">Forgot password?</a></p>

                        <button data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
                    </div>

                <div>
                <p class="mb-0">Don't have an account? <a href="#!" class="text-white-50 fw-bold">Sign Up</a>
                </p>
            </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>