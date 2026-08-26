import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function BranchSwitcher() {
    const {
        auth,
        activeCompany,
        activeStore,
        availableStores = [],
    } = usePage().props;

    const [switching, setSwitching] = useState(null);

    const canSwitch =
        Array.isArray(availableStores) &&
        availableStores.length > 1;

    const switchBranch = (storeId) => {
        if (!storeId || storeId === activeStore?.id) {
            return;
        }

        setSwitching(storeId);

        router.post(
            `/select-branch/${storeId}`,
            {},
            {
                preserveScroll: false,

                onFinish: () => {
                    setSwitching(null);
                },
            }
        );
    };

    if (!activeCompany || !activeStore) {
        return null;
    }

    return (
        <div className="dropdown rr-branch-switcher">

            <button
                type="button"
                className="rr-branch-switcher-button"
                data-bs-toggle={canSwitch ? 'dropdown' : undefined}
                aria-expanded="false"
            >

                <div className="rr-branch-company-icon">
                    <i className="bi bi-building"></i>
                </div>

                <div className="rr-branch-info">

                    <div className="rr-branch-company-name">
                        {activeCompany.company_name}
                    </div>

                    <div className="rr-branch-current">

                        <i className="bi bi-shop me-1"></i>

                        {activeStore.store_name}

                    </div>

                </div>

                {canSwitch && (
                    <i className="bi bi-chevron-down rr-branch-arrow"></i>
                )}

            </button>


            {canSwitch && (
                <div className="dropdown-menu rr-branch-menu">

                    <div className="rr-branch-menu-header">

                        <div className="rr-branch-menu-title">
                            Switch Branch
                        </div>

                        <div className="rr-branch-menu-company">
                            {activeCompany.company_name}
                        </div>

                    </div>

                    <div className="dropdown-divider"></div>

                    {availableStores.map((store) => {

                        const active =
                            Number(store.id) === Number(activeStore.id);

                        const loading =
                            Number(switching) === Number(store.id);

                        return (
                            <button
                                type="button"
                                className={`dropdown-item rr-branch-menu-item ${active
                                        ? 'rr-branch-menu-item-active'
                                        : ''
                                    }`}
                                key={store.id}
                                disabled={active || loading}
                                onClick={() => switchBranch(store.id)}
                            >

                                <span className="rr-branch-menu-icon">
                                    <i className="bi bi-shop"></i>
                                </span>

                                <span className="rr-branch-menu-content">

                                    <span className="rr-branch-menu-name">
                                        {store.store_name}
                                    </span>

                                    <span className="rr-branch-menu-code">
                                        {store.store_code}
                                    </span>

                                </span>

                                {loading ? (
                                    <span
                                        className="spinner-border spinner-border-sm"
                                    />
                                ) : active ? (
                                    <i className="bi bi-check2-circle rr-branch-check"></i>
                                ) : null}

                            </button>
                        );
                    })}

                </div>
            )}

        </div>
    );
}