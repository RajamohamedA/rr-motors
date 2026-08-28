import {
    useEffect,
    useState,
} from 'react';

import {
    usePage,
} from '@inertiajs/react';


export default function FlashNotification() {

    /*
    |--------------------------------------------------------------------------
    | Shared Application Message
    |--------------------------------------------------------------------------
    */

    const {
        app_message = null,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Notification State
    |--------------------------------------------------------------------------
    */

    const [
        notification,
        setNotification,
    ] = useState(null);


    /*
    |--------------------------------------------------------------------------
    | Watch Server Message
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                !app_message?.id ||
                !app_message?.message
            ) {

                return;
            }


            setNotification({
                id:
                    app_message.id,

                type:
                    app_message.type
                    ?? 'info',

                message:
                    app_message.message,
            });

        },
        [
            app_message?.id,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Auto Close
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                !notification
            ) {

                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        setNotification(
                            null
                        );

                    },
                    4500
                );


            return () => {

                window.clearTimeout(
                    timer
                );

            };

        },
        [
            notification?.id,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Hidden
    |--------------------------------------------------------------------------
    */

    if (
        !notification
    ) {

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Icons
    |--------------------------------------------------------------------------
    */

    const icons = {

        success:
            'bi-check-circle-fill',

        error:
            'bi-x-circle-fill',

        warning:
            'bi-exclamation-triangle-fill',

        info:
            'bi-info-circle-fill',

    };


    /*
    |--------------------------------------------------------------------------
    | Titles
    |--------------------------------------------------------------------------
    */

    const titles = {

        success:
            'Success',

        error:
            'Error',

        warning:
            'Warning',

        info:
            'Information',

    };


    const type =
        notification.type;


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <div className="rr-notification-container">

            <div
                key={
                    notification.id
                }
                className={
                    `rr-notification ` +
                    `rr-notification-${type}`
                }
                role="alert"
            >

                {/* =============================================
                    ICON
                   ============================================= */}

                <div className="rr-notification-icon">

                    <i
                        className={
                            `bi ${icons[type]
                            ?? icons.info
                            }`
                        }
                    ></i>

                </div>


                {/* =============================================
                    CONTENT
                   ============================================= */}

                <div className="rr-notification-content">

                    <div className="rr-notification-title">

                        {
                            titles[type]
                            ?? titles.info
                        }

                    </div>


                    <div className="rr-notification-message">

                        {
                            notification.message
                        }

                    </div>

                </div>


                {/* =============================================
                    CLOSE
                   ============================================= */}

                <button
                    type="button"
                    className="rr-notification-close"
                    onClick={() =>
                        setNotification(
                            null
                        )
                    }
                    aria-label="Close notification"
                >

                    <i className="bi bi-x-lg"></i>

                </button>

            </div>

        </div>

    );
}