import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Select({ company, stores }) {

    const [loadingStore, setLoadingStore] = useState(null);

    const selectBranch = (storeId) => {

        setLoadingStore(storeId);

        router.post(
            `/select-branch/${storeId}`,
            {},
            {
                onFinish: () => {
                    setLoadingStore(null);
                },
            }
        );
    };

    return (
        <>
            <Head title="Select Branch" />

            <div
                className="min-vh-100 d-flex align-items-center"
                style={{
                    background: '#f4f6f9',
                }}
            >

                <div className="container py-5">

                    {/* Header */}

                    <div className="text-center mb-5">

                        <div
                            className="mx-auto mb-3 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                            style={{
                                width: '70px',
                                height: '70px',
                            }}
                        >
                            <i className="bi bi-building fs-2"></i>
                        </div>

                        <h1 className="h3 fw-bold mb-2">
                            {company.company_name}
                        </h1>

                        <p className="text-muted mb-0">
                            Select the branch you want to work with
                        </p>

                    </div>


                    {/* Branches */}

                    <div className="row g-4 justify-content-center">

                        {stores.map((store) => {

                            const loading = loadingStore === store.id;

                            return (
                                <div
                                    className="col-xl-4 col-lg-6 col-md-6"
                                    key={store.id}
                                >

                                    <div className="card border-0 shadow-sm h-100">

                                        <div className="card-body p-4">

                                            <div className="d-flex align-items-start">

                                                <div
                                                    className="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                                    style={{
                                                        width: '55px',
                                                        height: '55px',
                                                        flexShrink: 0,
                                                    }}
                                                >
                                                    <i className="bi bi-shop fs-4"></i>
                                                </div>

                                                <div className="flex-grow-1">

                                                    <h5 className="fw-bold mb-1">
                                                        {store.store_name}
                                                    </h5>

                                                    <div className="text-muted small">
                                                        {store.store_code}
                                                    </div>

                                                </div>

                                                <span className="badge bg-success">
                                                    Active
                                                </span>

                                            </div>


                                            <hr className="my-4" />


                                            <div className="text-muted small">

                                                {store.contact_person && (
                                                    <div className="mb-2">
                                                        <i className="bi bi-person me-2"></i>
                                                        {store.contact_person}
                                                    </div>
                                                )}

                                                {store.phone && (
                                                    <div className="mb-2">
                                                        <i className="bi bi-telephone me-2"></i>
                                                        {store.phone}
                                                    </div>
                                                )}

                                                {store.city && (
                                                    <div>
                                                        <i className="bi bi-geo-alt me-2"></i>
                                                        {store.city}
                                                        {store.state
                                                            ? `, ${store.state}`
                                                            : ''
                                                        }
                                                    </div>
                                                )}

                                            </div>


                                            <button
                                                type="button"
                                                className="btn btn-primary w-100 mt-4"
                                                disabled={loading}
                                                onClick={() => selectBranch(store.id)}
                                            >

                                                {loading ? (
                                                    <>
                                                        <span
                                                            className="spinner-border spinner-border-sm me-2"
                                                        ></span>

                                                        Opening...
                                                    </>
                                                ) : (
                                                    <>
                                                        Enter Branch

                                                        <i className="bi bi-arrow-right ms-2"></i>
                                                    </>
                                                )}

                                            </button>

                                        </div>

                                    </div>

                                </div>
                            );
                        })}

                    </div>


                    {/* Logout */}

                    <div className="text-center mt-5">

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="btn btn-link text-muted text-decoration-none"
                        >
                            <i className="bi bi-box-arrow-left me-2"></i>
                            Logout
                        </Link>

                    </div>

                </div>

            </div>
        </>
    );
}