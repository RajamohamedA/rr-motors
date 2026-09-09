import React from "react";

import Create from "./Create";

export default function Edit({ quotation }) {
    return <Create quotation={quotation} mode="edit" />;
}

Edit.layout = Create.layout;
