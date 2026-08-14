<div class="card">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="bi bi-house"></i>
            {{ isset($godown) ? 'Edit Godown' : 'Create Godown' }}
        </h5>

    </div>

    <div class="card-body">

        <form action="{{ $route }}" method="POST">

            @csrf

            @if($method === 'PUT')
            @method('PUT')
            @endif

            <div class="row">

                {{-- Company --}}
                @if(auth()->user()->is_super_user)

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Company
                    </label>

                    <select
                        name="company_id"
                        id="company_id"
                        class="form-select"
                        required>

                        <option value="">
                            Select Company
                        </option>

                        @foreach($companies as $company)

                        <option
                            value="{{ $company->id }}"
                            {{ old(
                                        'company_id',
                                        $godown->company_id ?? ''
                                    ) == $company->id
                                        ? 'selected'
                                        : '' }}>
                            {{ $company->company_name }}
                        </option>

                        @endforeach

                    </select>

                    @error('company_id')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                @endif


                {{-- Store / Branch --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Branch / Store
                    </label>

                    <select
                        name="store_id"
                        id="store_id"
                        class="form-select"
                        required>

                        <option value="">
                            Select Branch
                        </option>

                        @foreach($stores as $store)

                        <option
                            value="{{ $store->id }}"
                            data-company="{{ $store->company_id }}"
                            {{ old(
                                    'store_id',
                                    $godown->store_id ?? ''
                                ) == $store->id
                                    ? 'selected'
                                    : '' }}>
                            {{ $store->store_name }}
                        </option>

                        @endforeach

                    </select>

                    @error('store_id')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Godown Name --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Godown Name
                    </label>

                    <input
                        type="text"
                        name="godown_name"
                        class="form-control"
                        value="{{ old(
                            'godown_name',
                            $godown->godown_name ?? ''
                        ) }}"
                        required>

                    @error('godown_name')
                    <div class="text-danger">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Code --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Godown Code
                    </label>

                    <input
                        type="text"
                        name="godown_code"
                        class="form-control"
                        value="{{ old(
                            'godown_code',
                            $godown->godown_code ?? ''
                        ) }}">

                </div>


                {{-- Address --}}
                <div class="col-md-12 mb-3">

                    <label class="form-label">
                        Address
                    </label>

                    <textarea
                        name="address"
                        class="form-control"
                        rows="3">{{ old(
                        'address',
                        $godown->address ?? ''
                    ) }}</textarea>

                </div>


                {{-- Status --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select">

                        <option
                            value="1"
                            {{ old(
                                'status',
                                $godown->status ?? 1
                            ) == 1
                                ? 'selected'
                                : '' }}>
                            Active
                        </option>

                        <option
                            value="0"
                            {{ old(
                                'status',
                                $godown->status ?? 1
                            ) == 0
                                ? 'selected'
                                : '' }}>
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            <div class="text-end">

                <a
                    href="{{ route('godowns.index') }}"
                    class="btn btn-secondary">
                    Back
                </a>

                <button
                    type="submit"
                    class="btn btn-success">
                    <i class="bi bi-check-circle"></i>
                    Save Godown
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const company = document.getElementById('company_id');
        const store = document.getElementById('store_id');

        if (!company || !store) {
            return;
        }

        function filterStores() {

            const companyId = company.value;

            Array.from(store.options).forEach(function(option) {

                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const optionCompany =
                    option.getAttribute('data-company');

                option.hidden =
                    companyId !== optionCompany;

            });


            /*
            |--------------------------------------------------------------------------
            | Reset invalid store
            |--------------------------------------------------------------------------
            */

            const selected =
                store.options[store.selectedIndex];

            if (
                selected &&
                selected.value &&
                selected.hidden
            ) {
                store.value = '';
            }
        }


        company.addEventListener(
            'change',
            filterStores
        );


        filterStores();

    });
</script>