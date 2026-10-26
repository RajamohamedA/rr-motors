import { Head } from '@inertiajs/react';
import SuperAdminLayout from '../../../Layouts/SuperAdminLayout';
import CompanyForm from './CompanyForm';

export default function Edit({ company, addressSelection = {} }) {
    return (
        <SuperAdminLayout
            title="Edit Company"
            subtitle={company.company_name}
            breadcrumbs={[
                { label: 'Companies', href: '/super-admin/companies' },
                {
                    label: company.company_name,
                    href: `/super-admin/companies/${company.id}`,
                },
                { label: 'Edit' },
            ]}
        >
            <Head title={`Edit ${company.company_name}`} />
            <CompanyForm
                key={company.id}
                company={company}
                addressSelection={addressSelection}
                submitUrl={`/super-admin/companies/${company.id}`}
                method="put"
                submitLabel="Save Changes"
            />
        </SuperAdminLayout>
    );
}
