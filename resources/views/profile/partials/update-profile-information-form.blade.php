<div class="card shadow">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Profile Information</h5>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Full Name</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="form-control"
                    required>
            </div>

            <button class="btn btn-primary">
                Save Changes
            </button>

        </form>

    </div>

</div>