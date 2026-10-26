import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{a as t,c as n,n as r,o as i,r as a,t as o}from"./react-app-Dpl9nMhu.js";import{t as s}from"./BusinessLayout-tkFlW2Yw.js";import{t as c}from"./MasterNavigation-BDJJYCQ4.js";import{t as l}from"./ConfirmModal-D6xSJL21.js";/* empty css                  */var u=e(n(),1),d=o();function f({mechanic:e,recent_entries:n=[]}){let{auth:o}=t().props,s=o?.user,f=s?.permissions??[],x=s?.roles??[],S=!!s?.is_super_user,C=x.includes(`Company Admin`),w=e=>S||C?!0:f.includes(e),[T,E]=(0,u.useState)(!1),[D,O]=(0,u.useState)(!1);return(0,d.jsxs)(d.Fragment,{children:[(0,d.jsx)(r,{title:e.mechanic_name}),(0,d.jsxs)(`div`,{className:`rr-mec-page rr-mec-show-page`,children:[(0,d.jsx)(`style`,{children:`
                    .rr-mec-show-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-show-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-show-page .rr-master-nav-shell,
                    .rr-mec-show-page .rr-mec-profile-head,
                    .rr-mec-show-page .rr-mec-summary,
                    .rr-mec-show-page .rr-mec-show-grid,
                    .rr-mec-show-page .rr-mec-table-card,
                    .rr-mec-show-page .rr-mec-detail-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-show-page .rr-master-nav-shell {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-profile-head,
                    .rr-mec-show-page .rr-mec-summary,
                    .rr-mec-show-page .rr-mec-show-grid,
                    .rr-mec-show-page .rr-mec-table-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-profile-head {
                        padding: 12px 14px !important;
                    }

                    .rr-mec-show-page .rr-mec-summary {
                        gap: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-show-grid {
                        gap: 8px !important;
                    }

                    @media (max-width: 760px) {
                        .rr-mec-show-page {
                            padding: 3px !important;
                        }
                    }
                `}),(0,d.jsxs)(`div`,{className:`rr-mec-workspace`,style:{width:`100%`,maxWidth:`none`,margin:0},children:[(0,d.jsx)(c,{active:`mechanics`,title:`Mechanic Details`,subtitle:`Profile, commission payable and recent ledger activity`}),(0,d.jsxs)(`section`,{className:`rr-mec-profile-head`,children:[(0,d.jsx)(`div`,{className:`rr-mec-large-avatar`,children:_(e.mechanic_name)}),(0,d.jsxs)(`div`,{className:`rr-mec-profile-name`,children:[(0,d.jsx)(`span`,{children:e.mechanic_code}),(0,d.jsx)(`h1`,{children:e.mechanic_name}),(0,d.jsxs)(`p`,{children:[e.mobile||`No mobile number`,e.city?` • ${e.city}`:``]})]}),(0,d.jsxs)(`div`,{className:`rr-mec-profile-actions`,children:[(0,d.jsxs)(a,{className:`rr-mec-btn secondary`,href:`/mechanics`,children:[(0,d.jsx)(`i`,{className:`bi bi-arrow-left`}),`Back`]}),w(`mechanic.edit`)&&(0,d.jsxs)(`button`,{className:`rr-mec-btn ${e.status?`warning`:`success`}`,type:`button`,onClick:()=>E(!0),children:[(0,d.jsx)(`i`,{className:`bi ${e.status?`bi-pause-circle`:`bi-play-circle`}`}),e.status?`Deactivate`:`Activate`]}),w(`mechanic.edit`)&&(0,d.jsxs)(a,{className:`rr-mec-btn primary`,href:`/mechanics/${e.id}/edit`,children:[(0,d.jsx)(`i`,{className:`bi bi-pencil-square`}),`Edit Mechanic`]})]})]}),(0,d.jsxs)(`section`,{className:`rr-mec-summary`,children:[(0,d.jsx)(m,{label:`Current Payable`,value:e.balance_label??`₹0.00`,icon:`bi-wallet2`,tone:`purple`}),(0,d.jsx)(m,{label:`Sales`,value:e.sales_count??0,icon:`bi-receipt`,tone:`blue`}),(0,d.jsx)(m,{label:`Commissions`,value:e.commissions_count??0,icon:`bi-percent`,tone:`green`}),(0,d.jsx)(m,{label:`Payments`,value:e.payments_count??0,icon:`bi-cash-stack`,tone:`red`})]}),(0,d.jsxs)(`div`,{className:`rr-mec-show-grid`,children:[(0,d.jsxs)(`section`,{className:`rr-mec-detail-card`,children:[(0,d.jsx)(h,{icon:`bi-person-vcard`,text:`Mechanic Information`}),(0,d.jsxs)(`dl`,{children:[(0,d.jsx)(g,{label:`Mechanic Code`,value:e.mechanic_code}),(0,d.jsx)(g,{label:`Mobile`,value:e.mobile}),(0,d.jsx)(g,{label:`Email`,value:e.email}),(0,d.jsx)(g,{label:`Address`,value:e.address}),(0,d.jsx)(g,{label:`City`,value:e.city}),(0,d.jsx)(g,{label:`State`,value:e.state}),(0,d.jsx)(g,{label:`Pincode`,value:e.pincode}),(0,d.jsxs)(`div`,{children:[(0,d.jsx)(`dt`,{children:`Status`}),(0,d.jsx)(`dd`,{children:(0,d.jsx)(`span`,{className:`rr-mec-status ${e.status?`active`:`inactive`}`,children:e.status?`Active`:`Inactive`})})]})]})]}),(0,d.jsxs)(`section`,{className:`rr-mec-detail-card`,children:[(0,d.jsx)(h,{icon:`bi-lightning-charge`,text:`Quick Actions`}),(0,d.jsxs)(`div`,{className:`rr-mec-quick-actions`,children:[w(`mechanic-payment.create`)&&(0,d.jsxs)(a,{href:`/mechanic-payments/create?mechanic_id=${e.id}`,style:p.payment,children:[(0,d.jsx)(`i`,{className:`bi bi-cash-stack`}),(0,d.jsxs)(`span`,{children:[(0,d.jsx)(`strong`,{children:`Record Payment`}),(0,d.jsx)(`small`,{children:`Pay commission balance`})]}),(0,d.jsx)(`i`,{className:`bi bi-chevron-right ms-auto`})]}),w(`mechanic-ledger.view`)&&(0,d.jsxs)(a,{href:`/mechanic-ledger?mechanic_id=${e.id}`,style:p.ledger,children:[(0,d.jsx)(`i`,{className:`bi bi-journal-text`}),(0,d.jsxs)(`span`,{children:[(0,d.jsx)(`strong`,{children:`Open Ledger`}),(0,d.jsx)(`small`,{children:`View all transactions`})]}),(0,d.jsx)(`i`,{className:`bi bi-chevron-right ms-auto`})]}),(0,d.jsxs)(a,{href:`/mechanics`,style:p.list,children:[(0,d.jsx)(`i`,{className:`bi bi-people`}),(0,d.jsxs)(`span`,{children:[(0,d.jsx)(`strong`,{children:`Mechanic Register`}),(0,d.jsx)(`small`,{children:`Return to mechanic list`})]}),(0,d.jsx)(`i`,{className:`bi bi-chevron-right ms-auto`})]})]})]})]}),(0,d.jsxs)(`section`,{className:`rr-mec-table-card`,children:[(0,d.jsx)(h,{icon:`bi-clock-history`,text:`Recent Ledger Entries`}),(0,d.jsx)(`div`,{className:`rr-mec-table-wrap`,children:(0,d.jsxs)(`table`,{children:[(0,d.jsx)(`thead`,{children:(0,d.jsxs)(`tr`,{children:[(0,d.jsx)(`th`,{children:`Date`}),(0,d.jsx)(`th`,{children:`Type`}),(0,d.jsx)(`th`,{children:`Reference`}),(0,d.jsx)(`th`,{children:`Remarks`}),(0,d.jsx)(`th`,{className:`number`,children:`Debit`}),(0,d.jsx)(`th`,{className:`number`,children:`Credit`})]})}),(0,d.jsx)(`tbody`,{children:n.length?n.map(e=>(0,d.jsxs)(`tr`,{children:[(0,d.jsx)(`td`,{children:y(e.date??e.transaction_date)}),(0,d.jsx)(`td`,{children:(0,d.jsx)(`span`,{className:`rr-mec-ledger-type`,children:b(e.type??e.transaction_type)})}),(0,d.jsx)(`td`,{children:e.reference_no||`—`}),(0,d.jsx)(`td`,{children:e.remarks||`—`}),(0,d.jsx)(`td`,{className:`number debit`,children:Number(e.debit)>0?v(e.debit):`—`}),(0,d.jsx)(`td`,{className:`number credit`,children:Number(e.credit)>0?v(e.credit):`—`})]},e.id)):(0,d.jsx)(`tr`,{children:(0,d.jsx)(`td`,{colSpan:`6`,children:(0,d.jsxs)(`div`,{className:`rr-mec-empty`,children:[(0,d.jsx)(`i`,{className:`bi bi-journal-x`}),(0,d.jsx)(`strong`,{children:`No ledger entries available`}),(0,d.jsx)(`span`,{children:`Transactions will appear here after commissions or payments.`})]})})})})]})})]}),(0,d.jsx)(l,{show:T,title:e.status?`Deactivate Mechanic?`:`Activate Mechanic?`,message:e.status?`${e.mechanic_name} will no longer be available for new branch transactions.`:`${e.mechanic_name} will become available for new branch transactions.`,confirmText:e.status?`Deactivate`:`Activate`,cancelText:`Cancel`,icon:e.status?`bi-pause-circle`:`bi-play-circle`,variant:e.status?`danger`:`primary`,processing:D,onConfirm:()=>{D||i.patch(`/mechanics/${e.id}/toggle-status`,{},{preserveScroll:!0,onStart:()=>O(!0),onFinish:()=>O(!1),onSuccess:()=>E(!1)})},onCancel:()=>{D||E(!1)}})]})]})]})}var p={payment:{borderLeft:`4px solid #1f8a56`,background:`#eef9f3`,color:`#185f3e`},ledger:{borderLeft:`4px solid #7050b1`,background:`#f4f0fb`,color:`#52358d`},list:{borderLeft:`4px solid #2670b8`,background:`#eef5fc`,color:`#20598f`}};function m({label:e,value:t,icon:n,tone:r}){return(0,d.jsxs)(`article`,{className:`rr-mec-summary-card ${r}`,children:[(0,d.jsx)(`span`,{children:(0,d.jsx)(`i`,{className:`bi ${n}`})}),(0,d.jsxs)(`div`,{children:[(0,d.jsx)(`small`,{children:e}),(0,d.jsx)(`strong`,{children:t??0})]})]})}function h({icon:e,text:t}){return(0,d.jsxs)(`div`,{className:`rr-mec-card-title`,children:[(0,d.jsx)(`i`,{className:`bi ${e}`}),(0,d.jsx)(`strong`,{children:t})]})}function g({label:e,value:t}){return(0,d.jsxs)(`div`,{children:[(0,d.jsx)(`dt`,{children:e}),(0,d.jsx)(`dd`,{children:t||`—`})]})}function _(e){return String(e??`M`).trim().split(/\s+/).slice(0,2).map(e=>e[0]).join(``).toUpperCase()}function v(e){return new Intl.NumberFormat(`en-IN`,{style:`currency`,currency:`INR`,minimumFractionDigits:2}).format(Number(e??0))}function y(e){if(!e)return`—`;let t=String(e).slice(0,10),n=new Date(`${t}T00:00:00`);return Number.isNaN(n.getTime())?`—`:n.toLocaleDateString(`en-IN`)}function b(e){return e?String(e).replaceAll(`_`,` `).replace(/\b\w/g,e=>e.toUpperCase()):`—`}f.layout=e=>(0,d.jsx)(s,{children:e});export{f as default};