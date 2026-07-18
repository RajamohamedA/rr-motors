<div class="card shadow">

    <div class="card-header bg-warning">
        <h5 class="mb-0">Change Password</h5>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Current Password</label>

                <input
                    type="password"
                    name="current_password"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>New Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control">
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control">
            </div>

            <button class="btn btn-warning">
                Update Password
            </button>

        </form>

    </div>

</div>