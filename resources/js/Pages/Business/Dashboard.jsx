import { Head, usePage } from '@inertiajs/react';

import BusinessLayout from '../../Layouts/BusinessLayout';

export default function Dashboard() {

    const {
        auth,
        activeCompany,
        activeStore,
    } = usePage().props;

    return (
        <>
            <Head title="Dashboard" />

            <div className="rr-page-header">

                <div>

                    <h1 className="rr-page-title">
                        Dashboard
                    </h1>

                    <p className="rr-page-description">
                        Welcome back, {auth?.user?.name}.
                        Here's an overview of {activeStore?.store_name}.
                    </p>

                </div>

            </div>


            <div className="row g-4">

                {/* Sales */}

                <div className="col-xl-3 col-md-6">

                    <div className="rr-card h-100">

                        <div className="rr-card-body">

                            <div className="d-flex justify-content-between">

                                <div>

                                    <div className="text-muted small">
                                        Today's Sales
                                    </div>

                                    <div className="fs-3 fw-bold mt-2">
                                        ₹0.00
                                    </div>

                                </div>

                                <div
                                    className="
                                        d-flex
                                        align-items-center
                                        justify-content-center
                                        rounded-3
                                        bg-primary
                                        bg-opacity-10
                                        text-primary
                                    "
                                    style={{
                                        width: '44px',
                                        height: '44px',
                                    }}
                                >
                                    <i className="bi bi-cart-check fs-5"></i>
                                </div>

                            </div>

                            <div className="small text-muted mt-3">
                                No sales recorded today
                            </div>

                        </div>

                    </div>

                </div>


                {/* Purchase */}

                <div className="col-xl-3 col-md-6">

                    <div className="rr-card h-100">

                        <div className="rr-card-body">

                            <div className="d-flex justify-content-between">

                                <div>

                                    <div className="text-muted small">
                                        Today's Purchase
                                    </div>

                                    <div className="fs-3 fw-bold mt-2">
                                        ₹0.00
                                    </div>

                                </div>

                                <div
                                    className="
                                        d-flex
                                        align-items-center
                                        justify-content-center
                                        rounded-3
                                        bg-success
                                        bg-opacity-10
                                        text-success
                                    "
                                    style={{
                                        width: '44px',
                                        height: '44px',
                                    }}
                                >
                                    <i className="bi bi-bag-check fs-5"></i>
                                </div>

                            </div>

                            <div className="small text-muted mt-3">
                                No purchases recorded today
                            </div>

                        </div>

                    </div>

                </div>


                {/* Stock */}

                <div className="col-xl-3 col-md-6">

                    <div className="rr-card h-100">

                        <div className="rr-card-body">

                            <div className="d-flex justify-content-between">

                                <div>

                                    <div className="text-muted small">
                                        Stock Products
                                    </div>

                                    <div className="fs-3 fw-bold mt-2">
                                        0
                                    </div>

                                </div>

                                <div
                                    className="
                                        d-flex
                                        align-items-center
                                        justify-content-center
                                        rounded-3
                                        bg-warning
                                        bg-opacity-10
                                        text-warning
                                    "
                                    style={{
                                        width: '44px',
                                        height: '44px',
                                    }}
                                >
                                    <i className="bi bi-box-seam fs-5"></i>
                                </div>

                            </div>

                            <div className="small text-muted mt-3">
                                Current branch inventory
                            </div>

                        </div>

                    </div>

                </div>


                {/* Customers */}

                <div className="col-xl-3 col-md-6">

                    <div className="rr-card h-100">

                        <div className="rr-card-body">

                            <div className="d-flex justify-content-between">

                                <div>

                                    <div className="text-muted small">
                                        Customers
                                    </div>

                                    <div className="fs-3 fw-bold mt-2">
                                        0
                                    </div>

                                </div>

                                <div
                                    className="
                                        d-flex
                                        align-items-center
                                        justify-content-center
                                        rounded-3
                                        bg-info
                                        bg-opacity-10
                                        text-info
                                    "
                                    style={{
                                        width: '44px',
                                        height: '44px',
                                    }}
                                >
                                    <i className="bi bi-people fs-5"></i>
                                </div>

                            </div>

                            <div className="small text-muted mt-3">
                                Registered customers
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {/* Lower area */}

            <div className="row g-4 mt-1">

                <div className="col-xl-8">

                    <div className="rr-card">

                        <div className="rr-card-header">

                            <div>

                                <h2 className="rr-card-title">
                                    Sales Overview
                                </h2>

                                <div className="rr-card-subtitle">
                                    Sales performance for this branch
                                </div>

                            </div>

                        </div>

                        <div className="rr-card-body">

                            <div
                                className="
                                    d-flex
                                    align-items-center
                                    justify-content-center
                                    text-muted
                                "
                                style={{
                                    height: '260px',
                                }}
                            >
                                Chart will be added here
                            </div>

                        </div>

                    </div>

                </div>


                <div className="col-xl-4">

                    <div className="rr-card">

                        <div className="rr-card-header">

                            <div>

                                <h2 className="rr-card-title">
                                    Quick Actions
                                </h2>

                                <div className="rr-card-subtitle">
                                    Common business tasks
                                </div>

                            </div>

                        </div>

                        <div className="rr-card-body">

                            <div className="d-grid gap-2">

                                <a
                                    href="/sales/create"
                                    className="btn btn-primary"
                                >
                                    <i className="bi bi-plus-lg me-2"></i>
                                    New Sale
                                </a>

                                <a
                                    href="/purchases/create"
                                    className="btn btn-outline-secondary"
                                >
                                    <i className="bi bi-bag-plus me-2"></i>
                                    New Purchase
                                </a>

                                <a
                                    href="/stock-transfers/create"
                                    className="btn btn-outline-secondary"
                                >
                                    <i className="bi bi-arrow-left-right me-2"></i>
                                    Stock Transfer
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </>
    );
}


Dashboard.layout = (page) => (
    <BusinessLayout>
        {page}
    </BusinessLayout>
);