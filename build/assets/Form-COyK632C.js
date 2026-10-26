import"./rolldown-runtime-QTnfLwEv.js";import{c as e,r as t,t as n}from"./react-app-Dpl9nMhu.js";e();var r=n();function i({data:e,setData:n,errors:i={},processing:s,onSubmit:c,mode:l=`create`}){let u=(e,t)=>{n(e,t)},d=l===`edit`;return(0,r.jsxs)(`form`,{className:`rr-mec-form-card rr-mec-pro-form`,onSubmit:c,noValidate:!0,children:[(0,r.jsx)(`style`,{children:`
                .rr-mec-pro-form {
                    overflow: hidden;
                    border: 1px solid #aebdcd !important;
                    border-radius: 5px !important;
                    background: #f4f7fa !important;
                    box-shadow: 0 2px 8px rgba(24, 48, 75, .10) !important;
                }

                .rr-mec-pro-form .rr-mec-form-head {
                    display: flex;
                    align-items: center;
                    min-height: 54px;
                    padding: 9px 13px !important;
                    border-bottom: 1px solid #aebdcd;
                    background: linear-gradient(#ffffff, #e7eef6);
                }

                .rr-mec-pro-form .rr-mec-form-head-copy {
                    min-width: 0;
                }

                .rr-mec-pro-form .rr-mec-form-head-copy strong {
                    color: #173b60;
                    font-size: 14px;
                }

                .rr-mec-pro-form .rr-mec-form-head-copy span {
                    color: #5d6f84;
                    font-size: 11px;
                }

                .rr-mec-form-head-status {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-left: auto;
                    padding: 4px 9px;
                    border: 1px solid ${e.status?`#8ac5a7`:`#dda5a8`};
                    border-radius: 12px;
                    background: ${e.status?`#e5f5ec`:`#fceced`};
                    color: ${e.status?`#176d45`:`#9c3036`};
                    font-size: 10px;
                    font-weight: 800;
                }

                .rr-mec-form-head-status::before {
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                    background: currentColor;
                    content: "";
                }

                .rr-mec-pro-layout {
                    display: grid;
                    grid-template-columns: minmax(0, 1.45fr) minmax(330px, .8fr);
                    align-items: start;
                    gap: 10px;
                    padding: 10px;
                }

                .rr-mec-pro-column {
                    display: grid;
                    gap: 10px;
                    min-width: 0;
                }

                .rr-mec-pro-section {
                    overflow: hidden;
                    border: 1px solid #c2ceda;
                    border-radius: 5px;
                    background: #ffffff;
                }

                .rr-mec-pro-section-head {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    min-height: 37px;
                    padding: 0 11px;
                    border-bottom: 1px solid #cbd6e1;
                    background: linear-gradient(#fbfdff, #edf2f7);
                    color: #274766;
                    font-size: 11px;
                    font-weight: 800;
                    letter-spacing: .25px;
                    text-transform: uppercase;
                }

                .rr-mec-pro-section-head i {
                    color: #2469ad;
                    font-size: 14px;
                }

                .rr-mec-pro-fields {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 10px;
                    padding: 11px;
                }

                .rr-mec-pro-fields.one-column {
                    grid-template-columns: 1fr;
                }

                .rr-mec-pro-form .rr-mec-field {
                    min-width: 0;
                }

                .rr-mec-pro-form .rr-mec-field > label {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    min-height: 17px;
                    margin-bottom: 4px;
                    color: #3d526a;
                    font-size: 10px;
                    font-weight: 800;
                    letter-spacing: .22px;
                    text-transform: uppercase;
                }

                .rr-mec-pro-form .rr-mec-field > label i {
                    color: #5c7895;
                    font-size: 11px;
                }

                .rr-mec-pro-form .rr-mec-field label em {
                    color: #b72f37;
                    font-style: normal;
                }

                .rr-mec-input-shell {
                    position: relative;
                    display: flex;
                    align-items: center;
                    width: 100%;
                }

                .rr-mec-input-shell > i {
                    position: absolute;
                    left: 10px;
                    z-index: 1;
                    color: #61768d;
                    pointer-events: none;
                }

                .rr-mec-pro-form .rr-mec-input-shell input,
                .rr-mec-pro-form .rr-mec-field textarea {
                    width: 100%;
                    border: 1px solid #a8b8c8;
                    border-radius: 4px;
                    background: #ffffff;
                    color: #17283b;
                    font: 12px/1.4 Inter, "Segoe UI", Arial, sans-serif;
                    outline: 0;
                    cursor: pointer;
                    transition: border-color .14s, box-shadow .14s;
                }

                .rr-mec-pro-form .rr-mec-input-shell input {
                    height: 36px;
                    padding: 0 10px 0 32px;
                }

                .rr-mec-pro-form .rr-mec-field textarea {
                    min-height: 78px;
                    padding: 8px 10px;
                    resize: vertical;
                }

                .rr-mec-pro-form .rr-mec-input-shell input:hover,
                .rr-mec-pro-form .rr-mec-field textarea:hover {
                    border-color: #7897b6;
                }

                .rr-mec-pro-form .rr-mec-input-shell input:focus,
                .rr-mec-pro-form .rr-mec-field textarea:focus {
                    border-color: #2470ba;
                    box-shadow: 0 0 0 3px rgba(36, 112, 186, .13);
                }

                .rr-mec-pro-form .rr-mec-field.has-error input,
                .rr-mec-pro-form .rr-mec-field.has-error textarea {
                    border-color: #c7464d;
                    background: #fffafa;
                }

                .rr-mec-pro-form .error {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    margin-top: 4px;
                    color: #a92d34;
                    font-size: 10px;
                    font-weight: 650;
                }

                .rr-mec-code-hint {
                    display: block;
                    margin-top: 4px;
                    color: #718096;
                    font-size: 9px;
                }

                .rr-mec-status-panel {
                    padding: 11px;
                }

                .rr-mec-status-choice {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 10px;
                    border: 1px solid ${e.status?`#94cbb0`:`#d6aaad`};
                    border-radius: 5px;
                    background: ${e.status?`#edf8f2`:`#fff1f2`};
                    cursor: pointer;
                }

                .rr-mec-status-choice input {
                    position: absolute;
                    width: 1px;
                    height: 1px;
                    opacity: 0;
                }

                .rr-mec-status-choice .rr-mec-switch {
                    flex: 0 0 auto;
                }

                .rr-mec-status-copy strong,
                .rr-mec-status-copy small {
                    display: block;
                }

                .rr-mec-status-copy strong {
                    color: ${e.status?`#176a43`:`#923037`};
                    font-size: 12px;
                }

                .rr-mec-status-copy small {
                    margin-top: 1px;
                    color: #637386;
                    font-size: 10px;
                }

                .rr-mec-pro-form .rr-mec-form-actions {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 8px;
                    min-height: 55px;
                    padding: 9px 12px !important;
                    border-top: 1px solid #b8c6d4;
                    background: linear-gradient(#f6f8fb, #e7edf3);
                }

                .rr-mec-action-note {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    margin-right: auto;
                    color: #66778a;
                    font-size: 10px;
                }

                .rr-mec-pro-form .rr-mec-btn:disabled {
                    cursor: not-allowed;
                    opacity: .65;
                }

                @media (max-width: 1000px) {
                    .rr-mec-pro-layout {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 620px) {
                    .rr-mec-pro-layout {
                        padding: 7px;
                    }

                    .rr-mec-pro-fields {
                        grid-template-columns: 1fr;
                    }

                    .rr-mec-form-head-status,
                    .rr-mec-action-note {
                        display: none;
                    }

                    .rr-mec-pro-form .rr-mec-form-actions {
                        align-items: stretch;
                        flex-direction: column-reverse;
                    }

                    .rr-mec-pro-form .rr-mec-form-actions .rr-mec-btn {
                        width: 100%;
                    }
                }
            `}),(0,r.jsxs)(`div`,{className:`rr-mec-form-head`,children:[(0,r.jsx)(`div`,{className:`rr-mec-form-icon`,children:(0,r.jsx)(`i`,{className:d?`bi bi-pencil-square`:`bi bi-person-plus`})}),(0,r.jsxs)(`div`,{className:`rr-mec-form-head-copy`,children:[(0,r.jsx)(`strong`,{children:d?`Update Mechanic Master`:`New Mechanic Master`}),(0,r.jsx)(`span`,{children:`Enter identity, contact, address and branch availability details.`})]}),(0,r.jsx)(`span`,{className:`rr-mec-form-head-status`,children:e.status?`Active`:`Inactive`})]}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-layout`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-pro-column`,children:[(0,r.jsxs)(`section`,{className:`rr-mec-pro-section`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-pro-section-head`,children:[(0,r.jsx)(`i`,{className:`bi bi-person-vcard`}),`Basic Information`]}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-fields`,children:[(0,r.jsx)(a,{label:`Mechanic Code`,icon:`bi-upc-scan`,value:e.mechanic_code,error:i.mechanic_code,onChange:e=>u(`mechanic_code`,e.target.value.toUpperCase()),placeholder:`Automatically generated`,maxLength:30,autoComplete:`off`,hint:`Unique within the company`}),(0,r.jsx)(a,{label:`Mechanic Name`,icon:`bi-person`,value:e.mechanic_name,error:i.mechanic_name,onChange:e=>u(`mechanic_name`,e.target.value),placeholder:`Enter mechanic name`,maxLength:150,autoComplete:`name`,required:!0,autoFocus:!0})]})]}),(0,r.jsxs)(`section`,{className:`rr-mec-pro-section`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-pro-section-head`,children:[(0,r.jsx)(`i`,{className:`bi bi-telephone`}),`Contact Information`]}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-fields`,children:[(0,r.jsx)(a,{label:`Mobile Number`,icon:`bi-phone`,value:e.mobile,error:i.mobile,onChange:e=>u(`mobile`,e.target.value),placeholder:`Enter mobile number`,inputMode:`tel`,maxLength:20,autoComplete:`tel`}),(0,r.jsx)(a,{label:`Email Address`,icon:`bi-envelope`,type:`email`,value:e.email,error:i.email,onChange:e=>u(`email`,e.target.value),placeholder:`Enter email address`,maxLength:255,autoComplete:`email`})]})]})]}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-column`,children:[(0,r.jsxs)(`section`,{className:`rr-mec-pro-section`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-pro-section-head`,children:[(0,r.jsx)(`i`,{className:`bi bi-geo-alt`}),`Address Information`]}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-fields one-column`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-field ${i.address?`has-error`:``}`,children:[(0,r.jsxs)(`label`,{htmlFor:`mechanic-address`,children:[(0,r.jsx)(`i`,{className:`bi bi-signpost`}),`Address`]}),(0,r.jsx)(`textarea`,{id:`mechanic-address`,rows:`3`,value:e.address,onChange:e=>u(`address`,e.target.value),placeholder:`Door number, street and area`,maxLength:1e3,autoComplete:`street-address`}),(0,r.jsx)(o,{error:i.address})]}),(0,r.jsx)(a,{label:`City`,icon:`bi-buildings`,value:e.city,error:i.city,onChange:e=>u(`city`,e.target.value),placeholder:`City`,maxLength:100,autoComplete:`address-level2`}),(0,r.jsxs)(`div`,{className:`rr-mec-pro-fields`,style:{padding:0},children:[(0,r.jsx)(a,{label:`State`,icon:`bi-map`,value:e.state,error:i.state,onChange:e=>u(`state`,e.target.value),placeholder:`State`,maxLength:100,autoComplete:`address-level1`}),(0,r.jsx)(a,{label:`Pincode`,icon:`bi-mailbox`,value:e.pincode,error:i.pincode,onChange:e=>u(`pincode`,e.target.value),placeholder:`Pincode`,inputMode:`numeric`,maxLength:20,autoComplete:`postal-code`})]})]})]}),(0,r.jsxs)(`section`,{className:`rr-mec-pro-section`,children:[(0,r.jsxs)(`div`,{className:`rr-mec-pro-section-head`,children:[(0,r.jsx)(`i`,{className:`bi bi-toggle-on`}),`Availability`]}),(0,r.jsxs)(`div`,{className:`rr-mec-status-panel`,children:[(0,r.jsxs)(`label`,{className:`rr-mec-status-choice`,children:[(0,r.jsx)(`input`,{type:`checkbox`,checked:!!e.status,onChange:e=>u(`status`,e.target.checked)}),(0,r.jsx)(`span`,{className:`rr-mec-switch`}),(0,r.jsxs)(`span`,{className:`rr-mec-status-copy`,children:[(0,r.jsx)(`strong`,{children:e.status?`Active Mechanic`:`Inactive Mechanic`}),(0,r.jsx)(`small`,{children:e.status?`Available for sales, commissions and payments.`:`Hidden from new business transactions.`})]})]}),(0,r.jsx)(o,{error:i.status})]})]})]})]}),(0,r.jsxs)(`div`,{className:`rr-mec-form-actions`,children:[(0,r.jsxs)(`span`,{className:`rr-mec-action-note`,children:[(0,r.jsx)(`i`,{className:`bi bi-shield-check`}),`Fields are validated for the active company and branch.`]}),(0,r.jsxs)(t,{className:`rr-mec-btn secondary`,href:`/mechanics`,"aria-disabled":s,onClick:e=>{s&&e.preventDefault()},children:[(0,r.jsx)(`i`,{className:`bi bi-x-lg`}),`Cancel`]}),(0,r.jsxs)(`button`,{className:`rr-mec-btn primary`,type:`submit`,disabled:s,children:[s?(0,r.jsx)(`span`,{className:`spinner-border spinner-border-sm`}):(0,r.jsx)(`i`,{className:`bi bi-check2-circle`}),s?`Saving...`:d?`Update Mechanic`:`Save Mechanic`]})]})]})}function a({label:e,icon:t,error:n,hint:i,required:a=!1,type:s=`text`,...c}){let l=`mechanic-${e.toLowerCase().replaceAll(` `,`-`)}`;return(0,r.jsxs)(`div`,{className:`rr-mec-field ${n?`has-error`:``}`,children:[(0,r.jsxs)(`label`,{htmlFor:l,children:[t&&(0,r.jsx)(`i`,{className:`bi ${t}`}),e,a&&(0,r.jsx)(`em`,{children:`*`})]}),(0,r.jsxs)(`div`,{className:`rr-mec-input-shell`,children:[t&&(0,r.jsx)(`i`,{className:`bi ${t}`}),(0,r.jsx)(`input`,{id:l,type:s,"aria-invalid":!!n,...c})]}),(0,r.jsx)(o,{error:n}),!n&&i&&(0,r.jsx)(`small`,{className:`rr-mec-code-hint`,children:i})]})}function o({error:e}){return e?(0,r.jsxs)(`small`,{className:`error`,children:[(0,r.jsx)(`i`,{className:`bi bi-exclamation-circle`}),e]}):null}export{i as default};