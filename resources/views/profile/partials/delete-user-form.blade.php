<div class="card border-danger shadow">

    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Delete Account</h5>
    </div>

    <div class="card-body">

        <p class="text-danger">
            Deleting your account is permanent and cannot be undone.
        </p>

        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <div class="mb-3">
                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control">
            </div>

            <button class="btn btn-danger">
                Delete Account
            </button>

        </form>

    </div>

</div>