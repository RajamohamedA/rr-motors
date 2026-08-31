import {
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useMemo,
} from 'react';


export default function MasterNavigation({
    active = null,
    title = 'Master Data',
    subtitle = 'Manage business master records',
    showBack = true,
    fallbackBackUrl = '/business/dashboard',
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        auth,
        business,
    } = usePage().props;


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Access Helpers
    |--------------------------------------------------------------------------
    */

    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const can =
        permission => {

            if (
                isSuperAdmin ||
                isCompanyAdmin
            ) {

                return true;
            }


            return permissions.includes(
                permission
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Masters
    |--------------------------------------------------------------------------
    */

    const items =
        useMemo(
            () => [

                {
                    key:
                        'customers',

                    label:
                        'Customers',

                    icon:
                        'bi-person-vcard',

                    href:
                        '/customers',

                    createHref:
                        '/customers/create',

                    viewPermission:
                        'customer.view',

                    createPermission:
                        'customer.create',
                },

                {
                    key:
                        'suppliers',

                    label:
                        'Suppliers',

                    icon:
                        'bi-truck',

                    href:
                        '/suppliers',

                    createHref:
                        '/suppliers/create',

                    viewPermission:
                        'supplier.view',

                    createPermission:
                        'supplier.create',
                },

                {
                    key:
                        'products',

                    label:
                        'Products',

                    icon:
                        'bi-box-seam',

                    href:
                        '/products',

                    createHref:
                        '/products/create',

                    viewPermission:
                        'product.view',

                    createPermission:
                        'product.create',
                },

                {
                    key:
                        'categories',

                    label:
                        'Categories',

                    icon:
                        'bi-tags',

                    href:
                        '/categories',

                    createHref:
                        '/categories/create',

                    viewPermission:
                        'category.view',

                    createPermission:
                        'category.create',
                },

                {
                    key:
                        'brands',

                    label:
                        'Brands',

                    icon:
                        'bi-award',

                    href:
                        '/brands',

                    createHref:
                        '/brands/create',

                    viewPermission:
                        'brand.view',

                    createPermission:
                        'brand.create',
                },

                {
                    key:
                        'units',

                    label:
                        'Units',

                    icon:
                        'bi-rulers',

                    href:
                        '/units',

                    createHref:
                        '/units/create',

                    viewPermission:
                        'unit.view',

                    createPermission:
                        'unit.create',
                },

                {
                    key:
                        'godowns',

                    label:
                        'Godowns',

                    icon:
                        'bi-building',

                    href:
                        '/godowns',

                    createHref:
                        '/godowns/create',

                    viewPermission:
                        'godown.view',

                    createPermission:
                        'godown.create',
                },

            ],
            [
                permissions,
                roles,
                isSuperAdmin,
                isCompanyAdmin,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Visible Items
    |--------------------------------------------------------------------------
    */

    const visibleItems =
        items.filter(
            item =>
                can(
                    item.viewPermission
                )
        );


    /*
    |--------------------------------------------------------------------------
    | Back
    |--------------------------------------------------------------------------
    */

    const handleBack =
        () => {

            if (
                typeof window !==
                'undefined'
                &&
                window.history.length > 1
            ) {

                window.history.back();

                return;
            }


            router.visit(
                fallbackBackUrl
            );
        };


    return (

        <section className="rr-master-nav-shell">

            {/* =========================================================
                HEADER
               ========================================================= */}

            <div className="rr-master-nav-header">

                <div className="rr-master-nav-header-left">

                    {
                        showBack && (

                            <button
                                type="button"
                                className="rr-master-back-btn"
                                onClick={
                                    handleBack
                                }
                                title="Go back"
                            >

                                <i className="bi bi-arrow-left"></i>

                                <span>
                                    Back
                                </span>

                            </button>

                        )
                    }


                    <div className="rr-master-nav-title-block">

                        <div className="rr-master-nav-title-icon">

                            <i className="bi bi-database"></i>

                        </div>


                        <div>

                            <span>
                                Business Masters
                            </span>

                            <strong>
                                {title}
                            </strong>

                            <small>
                                {subtitle}
                            </small>

                        </div>

                    </div>

                </div>


                <div className="rr-master-nav-context">

                    <div>

                        <span>
                            Company
                        </span>

                        <strong>
                            {
                                business?.company_name
                                ?? 'Company'
                            }
                        </strong>

                    </div>


                    <i className="bi bi-chevron-right"></i>


                    <div>

                        <span>
                            Branch
                        </span>

                        <strong>
                            {
                                business?.store_name
                                ?? 'No Branch'
                            }
                        </strong>

                    </div>

                </div>

            </div>


            {/* =========================================================
                COMMAND BAR
               ========================================================= */}

            <div className="rr-master-commandbar">

                {
                    visibleItems.map(
                        item => {

                            const isActive =
                                active ===
                                item.key;


                            const canCreate =
                                can(
                                    item.createPermission
                                );


                            return (

                                <div
                                    key={
                                        item.key
                                    }
                                    className={
                                        `rr-master-command-item ${isActive
                                            ? 'active'
                                            : ''
                                        }`
                                    }
                                >

                                    <Link
                                        href={
                                            item.href
                                        }
                                        className="rr-master-command-main"
                                        title={
                                            item.label
                                        }
                                    >

                                        <i
                                            className={
                                                `bi ${item.icon}`
                                            }
                                        ></i>

                                        <span>
                                            {
                                                item.label
                                            }
                                        </span>

                                    </Link>


                                    {
                                        canCreate && (

                                            <Link
                                                href={
                                                    item.createHref
                                                }
                                                className="rr-master-command-create"
                                                title={
                                                    `Add ${item.label}`
                                                }
                                                aria-label={
                                                    `Add ${item.label}`
                                                }
                                            >

                                                <i className="bi bi-plus-lg"></i>

                                            </Link>

                                        )
                                    }

                                </div>

                            );
                        }
                    )
                }

            </div>


            {/* =========================================================
                ACTIVE CONTEXT STRIP
               ========================================================= */}

            <div className="rr-master-nav-footer">

                <div>

                    <i className="bi bi-folder2-open"></i>

                    <span>
                        Masters
                    </span>

                    <i className="bi bi-chevron-right"></i>

                    <strong>
                        {
                            visibleItems.find(
                                item =>
                                    item.key ===
                                    active
                            )?.label
                            ?? 'Master Data'
                        }
                    </strong>

                </div>


                <span className="rr-master-nav-hint">

                    <i className="bi bi-mouse"></i>

                    Use the top menu to switch masters quickly

                </span>

            </div>

        </section>
    );
}
