import {
    Link,
} from '@inertiajs/react';


export default function PageShell({
    eyebrow = null,
    title,
    description = null,
    breadcrumb = [],
    actions = null,
    icon = 'bi-grid',
    children,
}) {

    return (

        <div className="rr-page-shell">

            {/* =========================================================
                BREADCRUMB
               ========================================================= */}

            {
                breadcrumb.length > 0 && (

                    <nav
                        className="rr-page-breadcrumb"
                        aria-label="Breadcrumb"
                    >

                        <Link
                            href="/company/dashboard"
                            className="rr-page-breadcrumb-home"
                            title="Company Dashboard"
                        >
                            <i className="bi bi-house-door"></i>
                        </Link>


                        {
                            breadcrumb.map(
                                (
                                    item,
                                    index
                                ) => {

                                    const isLast =
                                        index ===
                                        breadcrumb.length - 1;


                                    return (

                                        <div
                                            className="rr-page-breadcrumb-item"
                                            key={
                                                `${item.label}-${index}`
                                            }
                                        >

                                            <i className="bi bi-chevron-right"></i>


                                            {
                                                item.href &&
                                                    !isLast
                                                    ? (

                                                        <Link
                                                            href={
                                                                item.href
                                                            }
                                                        >
                                                            {
                                                                item.label
                                                            }
                                                        </Link>

                                                    )
                                                    : (

                                                        <span
                                                            className={
                                                                isLast
                                                                    ? 'active'
                                                                    : ''
                                                            }
                                                        >
                                                            {
                                                                item.label
                                                            }
                                                        </span>

                                                    )
                                            }

                                        </div>

                                    );
                                }
                            )
                        }

                    </nav>

                )
            }


            {/* =========================================================
                PAGE HERO
               ========================================================= */}

            <section className="rr-page-hero">

                <div className="rr-page-hero-main">

                    <div className="rr-page-hero-icon">

                        <i
                            className={
                                `bi ${icon}`
                            }
                        ></i>

                    </div>


                    <div className="rr-page-hero-content">

                        {
                            eyebrow && (

                                <span className="rr-page-eyebrow">

                                    {eyebrow}

                                </span>

                            )
                        }


                        <h1>
                            {title}
                        </h1>


                        {
                            description && (

                                <p>
                                    {description}
                                </p>

                            )
                        }

                    </div>

                </div>


                {
                    actions && (

                        <div className="rr-page-hero-actions">

                            {actions}

                        </div>

                    )
                }

            </section>


            {/* =========================================================
                CONTENT
               ========================================================= */}

            <div className="rr-page-shell-content">

                {children}

            </div>

        </div>

    );
}