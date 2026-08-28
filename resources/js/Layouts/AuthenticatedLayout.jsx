import {
    usePage,
} from '@inertiajs/react';

import SuperAdminLayout
    from '@/Layouts/SuperAdminLayout';

import BusinessLayout
    from '@/Layouts/BusinessLayout';


export default function AuthenticatedLayout({
    children,
}) {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    const {
        auth,
    } = usePage().props;


    const user =
        auth?.user;


    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    if (
        user?.is_super_user
    ) {

        return (

            <SuperAdminLayout>

                {children}

            </SuperAdminLayout>

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Company / Store Users
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            {children}

        </BusinessLayout>

    );
}