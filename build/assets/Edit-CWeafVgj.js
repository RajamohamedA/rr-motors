import"./rolldown-runtime-QTnfLwEv.js";import{c as e,i as t,n,r,t as i}from"./react-app-Dpl9nMhu.js";import{t as a}from"./BusinessLayout-tkFlW2Yw.js";import{t as o}from"./MasterNavigation-BDJJYCQ4.js";import s from"./Form-COyK632C.js";/* empty css                  */e();var c=i();function l({mechanic:e,context:i={}}){let{data:a,setData:l,put:u,processing:d,errors:f}=t({mechanic_code:e?.mechanic_code??``,mechanic_name:e?.mechanic_name??``,mobile:e?.mobile??``,email:e?.email??``,address:e?.address??``,city:e?.city??``,state:e?.state??`Tamil Nadu`,pincode:e?.pincode??``,status:!!e?.status}),p=t=>{t.preventDefault(),!d&&u(`/mechanics/${e.id}`,{preserveScroll:!0})},m=Object.keys(f??{}).length;return(0,c.jsxs)(c.Fragment,{children:[(0,c.jsx)(n,{title:`Edit ${e.mechanic_name}`}),(0,c.jsxs)(`div`,{className:`rr-mec-page rr-mec-edit-page`,children:[(0,c.jsx)(`style`,{children:`
                    .rr-mec-edit-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-edit-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-edit-page .rr-master-nav-shell,
                    .rr-mec-edit-page .rr-mec-titlebar,
                    .rr-mec-edit-page .rr-mec-form-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-edit-page .rr-master-nav-shell,
                    .rr-mec-edit-page .rr-mec-titlebar,
                    .rr-mec-edit-page .rr-mec-form-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-edit-page .rr-mec-titlebar {
                        min-height: 76px;
                        padding: 11px 14px !important;
                    }

                    .rr-mec-edit-title-main {
                        display: flex;
                        align-items: center;
                        gap: 11px;
                        min-width: 0;
                    }

                    .rr-mec-edit-title-icon {
                        display: grid;
                        place-items: center;
                        flex: 0 0 42px;
                        width: 42px;
                        height: 42px;
                        border: 1px solid #d6b46e;
                        border-radius: 5px;
                        background: linear-gradient(#fffdf7, #f8ecd1);
                        color: #a5680d;
                        font-size: 19px;
                    }

                    .rr-mec-edit-context {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 7px 13px;
                        margin-top: 4px;
                        color: #53677e;
                        font-size: 11px;
                        font-weight: 650;
                    }

                    .rr-mec-edit-context span {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                    }

                    .rr-mec-edit-actions {
                        display: flex;
                        align-items: center;
                        gap: 7px;
                    }

                    .rr-mec-validation-alert {
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        width: 100%;
                        margin: 0 0 8px;
                        padding: 9px 12px;
                        border: 1px solid #e0a4a8;
                        border-left: 4px solid #b8323a;
                        border-radius: 5px;
                        background: #fff1f2;
                        color: #8f2930;
                    }

                    .rr-mec-validation-alert > i {
                        margin-top: 1px;
                        font-size: 17px;
                    }

                    .rr-mec-validation-alert strong,
                    .rr-mec-validation-alert span {
                        display: block;
                    }

                    .rr-mec-validation-alert span {
                        margin-top: 1px;
                        font-size: 11px;
                    }

                    @media (max-width: 760px) {
                        .rr-mec-edit-page {
                            padding: 3px !important;
                        }

                        .rr-mec-edit-page .rr-mec-titlebar {
                            align-items: stretch;
                        }

                        .rr-mec-edit-actions {
                            width: 100%;
                        }

                        .rr-mec-edit-actions .rr-mec-btn {
                            flex: 1;
                        }
                    }
                `}),(0,c.jsxs)(`div`,{className:`rr-mec-workspace`,children:[(0,c.jsx)(o,{active:`mechanics`,title:`Edit Mechanic`,subtitle:i.store_name?`Current branch: ${i.store_name}`:`Mechanic Master`}),(0,c.jsxs)(`section`,{className:`rr-mec-titlebar`,children:[(0,c.jsxs)(`div`,{className:`rr-mec-edit-title-main`,children:[(0,c.jsx)(`div`,{className:`rr-mec-edit-title-icon`,children:(0,c.jsx)(`i`,{className:`bi bi-pencil-square`})}),(0,c.jsxs)(`div`,{children:[(0,c.jsx)(`span`,{children:`MECHANIC MASTER`}),(0,c.jsxs)(`h1`,{children:[`Edit `,e.mechanic_name]}),(0,c.jsx)(`p`,{children:`Update mechanic identity, contact, address and availability details.`}),(0,c.jsxs)(`div`,{className:`rr-mec-edit-context`,children:[(0,c.jsxs)(`span`,{children:[(0,c.jsx)(`i`,{className:`bi bi-upc-scan`}),`Code: `,e.mechanic_code]}),i.store_name&&(0,c.jsxs)(`span`,{children:[(0,c.jsx)(`i`,{className:`bi bi-shop`}),`Branch: `,i.store_name]})]})]})]}),(0,c.jsxs)(`div`,{className:`rr-mec-edit-actions`,children:[(0,c.jsxs)(r,{className:`rr-mec-btn secondary`,href:`/mechanics`,children:[(0,c.jsx)(`i`,{className:`bi bi-list-ul`}),`List`]}),(0,c.jsxs)(r,{className:`rr-mec-btn primary`,href:`/mechanics/${e.id}`,children:[(0,c.jsx)(`i`,{className:`bi bi-eye`}),`View Mechanic`]})]})]}),m>0&&(0,c.jsxs)(`div`,{className:`rr-mec-validation-alert`,role:`alert`,children:[(0,c.jsx)(`i`,{className:`bi bi-exclamation-octagon-fill`}),(0,c.jsxs)(`div`,{children:[(0,c.jsxs)(`strong`,{children:[`Please correct `,m,` form `,m===1?`error`:`errors`,`.`]}),(0,c.jsx)(`span`,{children:`Review the highlighted fields before updating the mechanic.`})]})]}),(0,c.jsx)(s,{data:a,setData:l,errors:f,processing:d,onSubmit:p,mode:`edit`})]})]})]})}l.layout=e=>(0,c.jsx)(a,{children:e});export{l as default};