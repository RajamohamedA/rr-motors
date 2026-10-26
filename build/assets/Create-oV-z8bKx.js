import"./rolldown-runtime-QTnfLwEv.js";import{c as e,i as t,n,r,t as i}from"./react-app-Dpl9nMhu.js";import{t as a}from"./BusinessLayout-tkFlW2Yw.js";import{t as o}from"./MasterNavigation-BDJJYCQ4.js";import s from"./Form-COyK632C.js";/* empty css                  */e();var c=i();function l({next_code:e=``,context:r={}}){let{data:i,setData:a,post:l,processing:d,errors:f}=t({mechanic_code:e,mechanic_name:``,mobile:``,email:``,address:``,city:``,state:`Tamil Nadu`,pincode:``,status:!0}),p=e=>{e.preventDefault(),!d&&l(`/mechanics`,{preserveScroll:!0})},m=Object.keys(f??{}).length;return(0,c.jsxs)(c.Fragment,{children:[(0,c.jsx)(n,{title:`Add Mechanic`}),(0,c.jsxs)(`div`,{className:`rr-mec-page rr-mec-create-page`,children:[(0,c.jsx)(`style`,{children:`
                    .rr-mec-create-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-create-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-create-page .rr-master-nav-shell,
                    .rr-mec-create-page .rr-mec-titlebar,
                    .rr-mec-create-page .rr-mec-form-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-create-page .rr-master-nav-shell,
                    .rr-mec-create-page .rr-mec-titlebar,
                    .rr-mec-create-page .rr-mec-form-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-create-page .rr-mec-titlebar {
                        min-height: 76px;
                        padding: 11px 14px !important;
                    }

                    .rr-mec-create-page .rr-mec-form-section {
                        padding: 13px 14px !important;
                    }

                    .rr-mec-create-page .rr-mec-form-actions {
                        padding: 10px 14px !important;
                    }

                    .rr-mec-create-title-main {
                        display: flex;
                        align-items: center;
                        gap: 11px;
                        min-width: 0;
                    }

                    .rr-mec-create-title-icon {
                        display: grid;
                        place-items: center;
                        flex: 0 0 42px;
                        width: 42px;
                        height: 42px;
                        border: 1px solid #9bbadd;
                        border-radius: 5px;
                        background: linear-gradient(#f8fbff, #e1edf9);
                        color: #1d65aa;
                        font-size: 19px;
                    }

                    .rr-mec-branch-context {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        margin-top: 4px;
                        color: #53677e;
                        font-size: 11px;
                        font-weight: 650;
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
                        .rr-mec-create-page {
                            padding: 3px !important;
                        }

                        .rr-mec-create-page .rr-mec-titlebar {
                            align-items: stretch;
                        }

                        .rr-mec-create-page .rr-mec-titlebar > .rr-mec-btn {
                            width: 100%;
                        }
                    }
                `}),(0,c.jsxs)(`div`,{className:`rr-mec-workspace`,children:[(0,c.jsx)(o,{active:`mechanics`,title:`Add Mechanic`,subtitle:r.store_name?`Current branch: ${r.store_name}`:`Mechanic Master`}),(0,c.jsx)(u,{title:`Create Mechanic`,text:`Create the mechanic master and make it available in the active branch.`,branchName:r.store_name}),m>0&&(0,c.jsxs)(`div`,{className:`rr-mec-validation-alert`,role:`alert`,children:[(0,c.jsx)(`i`,{className:`bi bi-exclamation-octagon-fill`}),(0,c.jsxs)(`div`,{children:[(0,c.jsxs)(`strong`,{children:[`Please correct `,m,` form `,m===1?`error`:`errors`,`.`]}),(0,c.jsx)(`span`,{children:`Review the highlighted fields before saving the mechanic.`})]})]}),(0,c.jsx)(s,{data:i,setData:a,errors:f,processing:d,onSubmit:p,mode:`create`})]})]})]})}function u({title:e,text:t,branchName:n}){return(0,c.jsxs)(`section`,{className:`rr-mec-titlebar`,children:[(0,c.jsxs)(`div`,{className:`rr-mec-create-title-main`,children:[(0,c.jsx)(`div`,{className:`rr-mec-create-title-icon`,children:(0,c.jsx)(`i`,{className:`bi bi-person-plus-fill`})}),(0,c.jsxs)(`div`,{children:[(0,c.jsx)(`span`,{children:`MECHANIC MASTER`}),(0,c.jsx)(`h1`,{children:e}),(0,c.jsx)(`p`,{children:t}),n&&(0,c.jsxs)(`div`,{className:`rr-mec-branch-context`,children:[(0,c.jsx)(`i`,{className:`bi bi-shop`}),`Active Branch: `,n]})]})]}),(0,c.jsxs)(r,{className:`rr-mec-btn secondary`,href:`/mechanics`,children:[(0,c.jsx)(`i`,{className:`bi bi-list-ul`}),`Mechanic List`]})]})}l.layout=e=>(0,c.jsx)(a,{children:e});export{l as default};