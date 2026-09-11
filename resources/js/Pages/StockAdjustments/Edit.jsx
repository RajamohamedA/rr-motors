import React from "react";

import BusinessLayout from "@/Layouts/BusinessLayout";

import StockAdjustmentForm from "./Create";

export default function Edit(props) {
    return (
        <StockAdjustmentForm
            {...props}
            mode="edit"
        />
    );
}

Edit.layout = (page) => (
    <BusinessLayout title="Edit Stock Adjustment">
        {page}
    </BusinessLayout>
);
