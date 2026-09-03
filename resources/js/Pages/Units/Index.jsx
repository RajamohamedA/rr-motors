import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useMemo,
    useState,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/units.css';


export default function Index({
    units,
    summary,
    filters,
}) {

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


    const [
        search,
        setSearch,
    ] = useState(
        filters?.search
        ?? ''
    );


    const [
        status,
        setStatus,
    ] = useState(
        filters?.status
        ?? ''
    );


    const [
        type,
        setType,
    ] = useState(
        filters?.type
        ?? ''
    );


    const [
        confirmState,
        setConfirmState,
    ] = useState({
        open: false,
        unit: null,
        action: null,
    });


    const applyFilters =
        (
            overrides = {}
        ) => {

            router.get(
                '/units',
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,

                    type:
                        overrides.type
                        ?? type,
                },
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    const submitSearch =
        event => {

            event.preventDefault();

            applyFilters();
        };


    useEffect(
        () => {

            const currentStatus =
                String(
                    filters?.status
                    ?? ''
                );


            const currentType =
                String(
                    filters?.type
                    ?? ''
                );


            if (
                String(status) === currentStatus
                &&
                String(type) === currentType
            ) {
                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        applyFilters();

                    },
                    120
                );


            return () =>
                window.clearTimeout(
                    timer
                );

        },
        [
            status,
            type,
        ]
    );


    const clearFilters =
        () => {

            setSearch('');
            setStatus('');
            setType('');


            router.get(
                '/units',
                {},
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    const rows =
        units?.data
        ?? [];


    const paginationLinks =
        units?.links
        ?? [];


    const hasFilters =
        Boolean(
            search
            ||
            status !== ''
            ||
            type !== ''
        );


    const openUnit =
        unit => {

            if (
                !can(
                    'unit.view'
                )
            ) {
                return;
            }


            router.visit(
                `/units/${unit.id}`
            );
        };


    const requestStatusChange =
        unit => {

            if (
                !can(
                    'unit.edit'
                )
            ) {
                return;
            }


            setConfirmState({
                open:
                    true,

                unit,

                action:
                    unit.status
                        ? 'deactivate'
                        : 'activate',
            });
        };


    const requestDelete =
        unit => {

            if (
                !can(
                    'unit.delete'
                )
            ) {
                return;
            }


            if (
                unit.is_system_unit
            ) {
                return;
            }


            setConfirmState({
                open:
                    true,

                unit,

                action:
                    'delete',
            });
        };


    const closeConfirm =
        () => {

            setConfirmState({
                open:
                    false,

                unit:
                    null,

                action:
                    null,
            });
        };


    const confirmAction =
        () => {

            const unit =
                confirmState.unit;


            if (
                !unit
            ) {
                closeConfirm();

                return;
            }


            if (
                confirmState.action ===
                'delete'
            ) {

                router.delete(
                    `/units/${unit.id}`,
                    {
                        preserveScroll:
                            true,

                        onFinish:
                            closeConfirm,
                    }
                );


                return;
            }


            router.patch(
                `/units/${unit.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onFinish:
                        closeConfirm,
                }
            );
        };


    const confirmContent =
        useMemo(
            () => {

                const unit =
                    confirmState.unit;


                if (
                    !unit
                ) {
                    return {
                        title: '',
                        message: '',
                        confirmText: 'Confirm',
                        tone: 'warning',
                    };
                }


                if (
                    confirmState.action ===
                    'delete'
                ) {
                    return {
                        title:
                            'Delete Unit?',

                        message:
                            `Delete "${unit.unit_name}"? This is only allowed if no product is using this unit.`,

                        confirmText:
                            'Delete Unit',

                        tone:
                            'danger',
                    };
                }


                if (
                    confirmState.action ===
                    'deactivate'
                ) {
                    return {
                        title:
                            'Deactivate Unit?',

                        message:
                            `Deactivate "${unit.unit_name}" for this branch? Existing products will remain unchanged.`,

                        confirmText:
                            'Deactivate',

                        tone:
                            'warning',
                    };
                }


                return {
                    title:
                        'Activate Unit?',

                    message:
                        `Activate "${unit.unit_name}" for this branch?`,

                    confirmText:
                        'Activate',

                    tone:
                        'success',
                };
            },
            [
                confirmState,
            ]
        );


    return (

        <BusinessLayout>

            <Head
                title="Units"
            />


            <div className="rr-units-page">

                <MasterNavigation
                    active="units"
                    title="Units"
                    subtitle="Units of measurement for products"
                />


                <section className="rr-units-header">

                    <div className="rr-units-header-main">

                        <div className="rr-units-header-icon">

                            <i className="bi bi-rulers"></i>

                        </div>


                        <div>

                            <span className="rr-units-eyebrow">
                                Product Master
                            </span>


                            <h1>
                                Units
                            </h1>


                            <p>
                                Manage measurement units for {
                                    business?.store_name
                                    ?? 'the active branch'
                                }.
                            </p>

                        </div>

                    </div>


                    <div className="rr-units-header-actions">

                        <button
                            type="button"
                            className="rr-unit-toolbar-btn"
                            onClick={
                                () =>
                                    router.reload({
                                        preserveScroll:
                                            true,
                                    })
                            }
                        >

                            <i className="bi bi-arrow-clockwise"></i>

                            Refresh

                        </button>


                        {
                            can(
                                'unit.create'
                            ) && (

                                <Link
                                    href="/units/create"
                                    className="rr-unit-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Unit

                                </Link>

                            )
                        }

                    </div>

                </section>


                <section className="rr-units-summary">

                    <SummaryCard
                        label="Total Units"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-rulers"
                    />


                    <SummaryCard
                        label="System Units"
                        value={
                            summary?.system
                            ?? 0
                        }
                        icon="bi-shield-check"
                        tone="system"
                    />


                    <SummaryCard
                        label="Custom Units"
                        value={
                            summary?.custom
                            ?? 0
                        }
                        icon="bi-pencil-square"
                        tone="warning"
                    />


                    <SummaryCard
                        label="Active"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <SummaryCard
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-dash-circle"
                        tone="muted"
                    />

                </section>


                <section className="rr-units-panel">

                    <div className="rr-units-toolbar">

                        <form
                            className="rr-units-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-units-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search unit name, short name or description..."
                                    onChange={
                                        event =>
                                            setSearch(
                                                event.target.value
                                            )
                                    }
                                />


                                {
                                    search && (

                                        <button
                                            type="button"
                                            onClick={
                                                () => {

                                                    setSearch('');

                                                    applyFilters({
                                                        search: '',
                                                    });
                                                }
                                            }
                                        >

                                            <i className="bi bi-x-lg"></i>

                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-unit-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-units-filters">

                            <div className="rr-unit-filter">

                                <label>
                                    Type
                                </label>


                                <select
                                    value={
                                        type
                                    }
                                    onChange={
                                        event =>
                                            setType(
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="">
                                        All Units
                                    </option>

                                    <option value="system">
                                        System
                                    </option>

                                    <option value="custom">
                                        Custom
                                    </option>

                                </select>

                            </div>


                            <div className="rr-unit-filter">

                                <label>
                                    Status
                                </label>


                                <select
                                    value={
                                        status
                                    }
                                    onChange={
                                        event =>
                                            setStatus(
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="">
                                        All
                                    </option>

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </div>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-units-clear-btn"
                                        onClick={
                                            clearFilters
                                        }
                                    >

                                        <i className="bi bi-funnel"></i>

                                        Clear

                                    </button>

                                )
                            }

                        </div>

                    </div>


                    <div className="rr-units-grid-status">

                        <div>

                            <i className="bi bi-table"></i>

                            Showing

                            <strong>
                                {
                                    units?.from
                                    ?? 0
                                }–{
                                    units?.to
                                    ?? 0
                                }
                            </strong>

                            of

                            <strong>
                                {
                                    units?.total
                                    ?? 0
                                }
                            </strong>

                            units

                        </div>


                        <span>
                            20 rows per page
                        </span>

                    </div>


                    <div className="rr-units-table-wrap">

                        <table className="rr-units-table">

                            <thead>

                                <tr>

                                    <th>
                                        Unit
                                    </th>

                                    <th>
                                        Short Name
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

                                    <th className="center actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? (

                                            rows.map(
                                                unit => (

                                                    <UnitRow
                                                        key={
                                                            unit.id
                                                        }

                                                        unit={
                                                            unit
                                                        }

                                                        can={
                                                            can
                                                        }

                                                        onOpen={
                                                            () =>
                                                                openUnit(
                                                                    unit
                                                                )
                                                        }

                                                        onStatusChange={
                                                            () =>
                                                                requestStatusChange(
                                                                    unit
                                                                )
                                                        }

                                                        onDelete={
                                                            () =>
                                                                requestDelete(
                                                                    unit
                                                                )
                                                        }
                                                    />

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="6"
                                                >

                                                    <EmptyState
                                                        filtered={
                                                            hasFilters
                                                        }

                                                        canCreate={
                                                            can(
                                                                'unit.create'
                                                            )
                                                        }
                                                    />

                                                </td>

                                            </tr>

                                        )
                                }

                            </tbody>

                        </table>

                    </div>


                    {
                        Number(
                            units?.total
                            ?? 0
                        ) > 0 && (

                            <div className="rr-units-pagination">

                                <div>

                                    Page

                                    <strong>
                                        {
                                            units?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            units?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-units-pagination-links">

                                    {
                                        paginationLinks.map(
                                            (
                                                link,
                                                index
                                            ) => (

                                                <PaginationLink
                                                    key={
                                                        `${link.label}-${index}`
                                                    }
                                                    link={
                                                        link
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </div>

                            </div>

                        )
                    }

                </section>


                <ConfirmModal
                    show={
                        confirmState.open
                    }

                    title={
                        confirmContent.title
                    }

                    message={
                        confirmContent.message
                    }

                    confirmText={
                        confirmContent.confirmText
                    }

                    variant={
                        confirmContent.tone
                    }

                    onConfirm={
                        confirmAction
                    }

                    onCancel={
                        closeConfirm
                    }
                />

            </div>

        </BusinessLayout>
    );
}


function UnitRow({
    unit,
    can,
    onOpen,
    onStatusChange,
    onDelete,
}) {

    const canOpen =
        can(
            'unit.view'
        );


    return (

        <tr
            className={
                canOpen
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                canOpen
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <div className="rr-unit-name-cell">

                    <div
                        className={
                            `rr-unit-avatar ${unit.is_system_unit
                                ? 'system'
                                : ''
                            }`
                        }
                    >

                        {
                            getInitials(
                                unit.unit_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                unit.unit_name
                            }
                        </strong>


                        <span>
                            Unit of measurement
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <span className="rr-unit-short-name">

                    {
                        unit.short_name
                    }

                </span>

            </td>


            <td>

                <span
                    className={
                        `rr-unit-type ${unit.is_system_unit
                            ? 'system'
                            : 'custom'
                        }`
                    }
                >

                    <i
                        className={
                            unit.is_system_unit
                                ? 'bi bi-shield-check'
                                : 'bi bi-pencil'
                        }
                    ></i>

                    {
                        unit.is_system_unit
                            ? 'System'
                            : 'Custom'
                    }

                </span>

            </td>


            <td>

                <div className="rr-unit-description">

                    {
                        unit.description
                        || 'No description'
                    }

                </div>

            </td>


            <td className="center">

                <span
                    className={
                        `rr-unit-status ${unit.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        unit.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            <td
                className="center"
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                <div className="rr-unit-actions">

                    {
                        can(
                            'unit.view'
                        ) && (

                            <Link
                                href={
                                    `/units/${unit.id}`
                                }
                                className="rr-unit-action-btn view"
                                title="View unit"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'unit.edit'
                        ) && (

                            <Link
                                href={
                                    `/units/${unit.id}/edit`
                                }
                                className="rr-unit-action-btn edit"
                                title="Edit unit"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'unit.edit'
                        ) && (

                            <button
                                type="button"
                                className={
                                    `rr-unit-action-btn ${unit.status
                                        ? 'deactivate'
                                        : 'activate'
                                    }`
                                }
                                title={
                                    unit.status
                                        ? 'Deactivate unit'
                                        : 'Activate unit'
                                }
                                onClick={
                                    onStatusChange
                                }
                            >

                                <i
                                    className={
                                        unit.status
                                            ? 'bi bi-toggle-on'
                                            : 'bi bi-toggle-off'
                                    }
                                ></i>

                            </button>

                        )
                    }


                    {
                        can(
                            'unit.delete'
                        )
                        &&
                        !unit.is_system_unit && (

                            <button
                                type="button"
                                className="rr-unit-action-btn delete"
                                title="Delete custom unit"
                                onClick={
                                    onDelete
                                }
                            >

                                <i className="bi bi-trash"></i>

                            </button>

                        )
                    }

                </div>

            </td>

        </tr>
    );
}


function SummaryCard({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-unit-summary-card ${tone}`
            }
        >

            <div className="rr-unit-summary-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {value}
                </strong>

            </div>

        </div>
    );
}


function PaginationLink({
    link,
}) {

    const label =
        String(
            link?.label
            ?? ''
        )
            .replace(
                '&laquo;',
                '«'
            )
            .replace(
                '&raquo;',
                '»'
            );


    if (
        !link?.url
    ) {

        return (

            <span className="rr-unit-page-link disabled">

                {label}

            </span>
        );
    }


    return (

        <Link
            href={
                link.url
            }
            preserveScroll
            preserveState
            className={
                `rr-unit-page-link ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {label}

        </Link>
    );
}


function EmptyState({
    filtered,
    canCreate,
}) {

    return (

        <div className="rr-units-empty">

            <div className="rr-units-empty-icon">

                <i className="bi bi-rulers"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No units match your filters'
                        : 'No units found for this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change or clear the filters and try again.'
                        : 'Default system units and custom units will appear here.'
                }

            </span>


            {
                canCreate &&
                !filtered && (

                    <Link
                        href="/units/create"
                        className="rr-unit-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Custom Unit

                    </Link>

                )
            }

        </div>
    );
}


function getInitials(
    value
) {

    const text =
        String(
            value
            ?? ''
        )
            .trim();


    if (
        !text
    ) {
        return 'UN';
    }


    return text
        .split(
            /\s+/
        )
        .slice(
            0,
            2
        )
        .map(
            part =>
                part
                    .charAt(
                        0
                    )
                    .toUpperCase()
        )
        .join(
            ''
        );
}