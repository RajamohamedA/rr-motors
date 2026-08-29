import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import '../../../../css/business/notifications.css';


export default function NotificationIndex({
    notificationPage,
    summary,
}) {

    const notifications =
        notificationPage?.data
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Mark Read
    |--------------------------------------------------------------------------
    */

    const markRead =
        (notification) => {

            if (
                !notification?.id ||
                notification?.read
            ) {
                return;
            }


            router.post(
                `/notifications/${notification.id}/read`,
                {},
                {
                    preserveScroll:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Notification
    |--------------------------------------------------------------------------
    */

    const openNotification =
        (notification) => {

            if (
                !notification
            ) {
                return;
            }


            if (
                !notification.read
            ) {

                router.post(
                    `/notifications/${notification.id}/read`,
                    {},
                    {
                        preserveScroll:
                            true,

                        onSuccess:
                            () => {

                                if (
                                    notification.fallback_url
                                ) {

                                    router.visit(
                                        notification.fallback_url
                                    );
                                }
                            },
                    }
                );

                return;
            }


            if (
                notification.fallback_url
            ) {

                router.visit(
                    notification.fallback_url
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Mark All
    |--------------------------------------------------------------------------
    */

    const markAllRead =
        () => {

            if (
                (
                    summary?.unread
                    ?? 0
                ) === 0
            ) {
                return;
            }


            router.post(
                '/notifications/read-all',
                {},
                {
                    preserveScroll:
                        true,
                }
            );
        };


    return (

        <BusinessLayout>

            <Head title="Notifications" />


            <div className="rr-notification-page">

                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <div className="rr-notification-header">

                    <div>

                        <span className="rr-notification-eyebrow">
                            Activity Center
                        </span>

                        <h1>
                            Notifications
                        </h1>

                        <p>
                            Review updates, alerts and business activity.
                        </p>

                    </div>


                    <button
                        type="button"
                        className="rr-notification-mark-all"
                        onClick={
                            markAllRead
                        }
                        disabled={
                            (
                                summary?.unread
                                ?? 0
                            ) === 0
                        }
                    >

                        <i className="bi bi-check2-all"></i>

                        Mark All as Read

                    </button>

                </div>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <div className="rr-notification-summary">

                    <SummaryCard
                        label="Total"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-bell"
                    />

                    <SummaryCard
                        label="Unread"
                        value={
                            summary?.unread
                            ?? 0
                        }
                        icon="bi-envelope"
                    />

                    <SummaryCard
                        label="Read"
                        value={
                            summary?.read
                            ?? 0
                        }
                        icon="bi-envelope-open"
                    />

                </div>


                {/* =====================================================
                    LIST
                   ===================================================== */}

                <section className="rr-notification-panel">

                    {
                        notifications.length > 0
                            ? (

                                notifications.map(
                                    notification => (

                                        <button
                                            type="button"
                                            key={
                                                notification.id
                                            }
                                            className={
                                                `rr-notification-item ${notification.read
                                                    ? ''
                                                    : 'unread'
                                                }`
                                            }
                                            onClick={
                                                () =>
                                                    openNotification(
                                                        notification
                                                    )
                                            }
                                        >

                                            <div
                                                className={
                                                    `rr-notification-icon ` +
                                                    `rr-notification-level-${notification.level ?? 'info'}`
                                                }
                                            >

                                                <i
                                                    className={
                                                        `bi ${notification.icon
                                                        ?? 'bi-bell'
                                                        }`
                                                    }
                                                ></i>

                                            </div>


                                            <div className="rr-notification-content">

                                                <div className="rr-notification-title-row">

                                                    <strong>

                                                        {
                                                            notification.title
                                                        }

                                                    </strong>


                                                    {
                                                        !notification.read && (

                                                            <span className="rr-notification-unread-dot"></span>

                                                        )
                                                    }

                                                </div>


                                                {
                                                    notification.message && (

                                                        <p>

                                                            {
                                                                notification.message
                                                            }

                                                        </p>

                                                    )
                                                }


                                                <div className="rr-notification-meta">

                                                    <span>

                                                        <i className="bi bi-clock"></i>

                                                        {
                                                            notification.created_at_human
                                                            ?? ''
                                                        }

                                                    </span>


                                                    {
                                                        notification.category && (

                                                            <span>

                                                                {
                                                                    notification.category
                                                                }

                                                            </span>

                                                        )
                                                    }

                                                </div>

                                            </div>


                                            <i className="bi bi-chevron-right rr-notification-arrow"></i>

                                        </button>

                                    )
                                )

                            )
                            : (

                                <div className="rr-notification-empty">

                                    <i className="bi bi-bell-slash"></i>

                                    <h2>
                                        No notifications yet
                                    </h2>

                                    <p>
                                        Business alerts and updates will appear here.
                                    </p>

                                </div>

                            )
                    }

                </section>


                {/* =====================================================
                    PAGINATION
                   ===================================================== */}

                {
                    (
                        notificationPage?.links
                        ?? []
                    ).length > 3 && (

                        <div className="rr-notification-pagination">

                            {
                                notificationPage.links.map(
                                    (
                                        link,
                                        index
                                    ) => (

                                        link.url
                                            ? (

                                                <Link
                                                    key={
                                                        index
                                                    }
                                                    href={
                                                        link.url
                                                    }
                                                    className={
                                                        link.active
                                                            ? 'active'
                                                            : ''
                                                    }
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
                                                />

                                            )
                                            : (

                                                <span
                                                    key={
                                                        index
                                                    }
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
                                                />

                                            )

                                    )
                                )
                            }

                        </div>

                    )
                }

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    label,
    value,
    icon,
}) {

    return (

        <div className="rr-notification-summary-card">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>

            <span>
                {label}
            </span>

            <strong>
                {value}
            </strong>

        </div>
    );
}