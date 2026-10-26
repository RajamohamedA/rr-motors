import { Head } from '@inertiajs/react';
import SuperAdminLayout from '../../../Layouts/SuperAdminLayout';
import CompanyForm from './CompanyForm';

export default function Create() {
    return (
        <SuperAdminLayout
            title="New Company"
            subtitle="Create a company and its first branch"
            breadcrumbs={[
                { label: 'Companies', href: '/super-admin/companies' },
                { label: 'New Company' },
            ]}
        >
            <Head title="New Company" />
            <CompanyForm
                submitUrl="/super-admin/companies"
                method="post"
                submitLabel="Create Company"
            />
        </SuperAdminLayout>
    );
}
