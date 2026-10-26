import{r as e}from"./rolldown-runtime-QTnfLwEv.js";import{c as t,n,o as r,r as i,t as a}from"./react-app-Dpl9nMhu.js";var o=e(t(),1),s=a();function c({company:e,stores:t=[]}){let[a,c]=(0,o.useState)(null),[l,u]=(0,o.useState)(``),[d,f]=(0,o.useState)(``),p=(0,o.useRef)(!1),m=(0,o.useMemo)(()=>{let e=l.trim().toLowerCase();return t.filter(t=>[t.store_name,t.store_code,t.contact_person,t.phone,t.city,t.state].filter(Boolean).join(` `).toLowerCase().includes(e))},[l,t]),h=e=>{p.current||(p.current=!0,c(e),f(``),r.post(`/select-branch/${e}`,{},{onError:()=>{f(`Unable to open this branch. Please try again.`)},onFinish:()=>{p.current=!1,c(null)}}))},g=a!==null;return(0,s.jsxs)(s.Fragment,{children:[(0,s.jsx)(n,{title:`Select Branch`}),(0,s.jsx)(`style`,{children:`
                .branch-page {
                    --branch-primary: #2456c7;
                    --branch-text: #172b4d;
                    --branch-secondary: #52627a;
                    min-height: 100vh;
                    color: var(--branch-text);
                    font-size: 13px;
                    background:
                        radial-gradient(
                            ellipse at 8% 0%,
                            rgba(66, 115, 222, 0.12),
                            transparent 42%
                        ),
                        radial-gradient(
                            ellipse at 100% 90%,
                            rgba(26, 157, 143, 0.08),
                            transparent 38%
                        ),
                        #f3f6fb;
                }

                .branch-shell {
                    max-width: 1180px;
                    margin: 0 auto;
                    padding: 30px 24px 24px;
                }

                .branch-topbar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 16px;
                    margin-bottom: 26px;
                }

                .branch-brand {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    min-width: 0;
                }

                .branch-brand-icon {
                    display: grid;
                    place-items: center;
                    flex-shrink: 0;
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    color: #fff;
                    background: linear-gradient(135deg, #3269db, #173b91);
                    box-shadow: 0 6px 15px rgba(36, 86, 199, 0.18);
                    font-size: 21px;
                }

                .branch-company {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 750;
                    overflow-wrap: anywhere;
                }

                .branch-brand-caption {
                    margin-top: 3px;
                    color: var(--branch-secondary);
                    font-size: 12px;
                }

                .branch-logout {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    flex-shrink: 0;
                    padding: 9px 13px;
                    border: 1px solid #d6dfed;
                    border-radius: 8px;
                    background: #fff;
                    color: #34445d;
                    font-size: 13px;
                    font-weight: 600;
                    transition: background 160ms, border-color 160ms;
                }

                .branch-logout:hover:not(:disabled) {
                    background: #fff3f3;
                    border-color: #edb9b9;
                    color: #ab2929;
                }

                .branch-hero {
                    position: relative;
                    overflow: hidden;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 24px;
                    padding: 30px;
                    border-radius: 16px;
                    color: #fff;
                    background: linear-gradient(115deg, #15366e, #2456a9);
                    box-shadow: 0 12px 30px rgba(22, 53, 109, 0.14);
                    animation: branch-enter 450ms ease-out both;
                }

                .branch-hero::after {
                    position: absolute;
                    right: -70px;
                    top: -110px;
                    width: 330px;
                    height: 330px;
                    border: 45px solid rgba(255, 255, 255, 0.045);
                    border-radius: 50%;
                    content: '';
                    pointer-events: none;
                }

                .branch-hero-content,
                .branch-stat {
                    position: relative;
                    z-index: 1;
                }

                .branch-eyebrow {
                    display: inline-flex;
                    align-items: center;
                    gap: 7px;
                    margin-bottom: 12px;
                    color: #dbe9ff;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 1.2px;
                    text-transform: uppercase;
                }

                .branch-hero h1 {
                    margin: 0 0 9px;
                    font-size: clamp(23px, 3vw, 30px);
                    font-weight: 750;
                    letter-spacing: -0.6px;
                }

                .branch-hero p {
                    margin: 0;
                    color: #e0eaff;
                    line-height: 1.7;
                }

                .branch-stat {
                    flex-shrink: 0;
                    min-width: 140px;
                    padding: 17px 23px;
                    border: 1px solid rgba(255, 255, 255, 0.22);
                    border-radius: 12px;
                    background: rgba(255, 255, 255, 0.09);
                    text-align: center;
                }

                .branch-stat-number {
                    font-size: 33px;
                    font-weight: 750;
                    line-height: 1.2;
                }

                .branch-stat-label {
                    margin-top: 5px;
                    color: #e0eaff;
                    font-size: 12px;
                }

                .branch-toolbar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 16px;
                    margin: 28px 0 18px;
                }

                .branch-section-title {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 750;
                }

                .branch-section-caption {
                    margin: 5px 0 0;
                    color: var(--branch-secondary);
                    font-size: 12px;
                }

                .branch-search {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    width: 340px;
                    max-width: 100%;
                    padding: 0 12px;
                    border: 1px solid #cbd6e6;
                    border-radius: 9px;
                    background: #fff;
                    transition: border-color 160ms, box-shadow 160ms;
                }

                .branch-search:focus-within {
                    border-color: #4674d5;
                    box-shadow: 0 0 0 3px rgba(36, 86, 199, 0.1);
                }

                .branch-search > i {
                    color: #526b92;
                }

                .branch-search input {
                    width: 100%;
                    min-width: 0;
                    height: 42px;
                    border: 0;
                    outline: 0;
                    background: transparent;
                    color: var(--branch-text);
                    font-size: 13px;
                }

                .branch-search input::placeholder {
                    color: #65738a;
                }

                .branch-search-clear {
                    display: grid;
                    place-items: center;
                    padding: 4px;
                    border: 0;
                    border-radius: 4px;
                    background: transparent;
                    color: #52627a;
                }

                .branch-search-clear:hover {
                    background: #edf2fb;
                }

                .branch-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 18px;
                }

                .branch-card {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    min-width: 0;
                    overflow: hidden;
                    border: 1px solid #d9e2ef;
                    border-radius: 12px;
                    background: #fff;
                    box-shadow: 0 3px 10px rgba(28, 49, 84, 0.035);
                    transition:
                        transform 180ms ease,
                        box-shadow 180ms ease,
                        border-color 180ms ease;
                    animation: branch-enter 450ms ease-out both;
                    animation-delay: var(--branch-delay);
                }

                .branch-card::before {
                    height: 4px;
                    background: var(--branch-accent);
                    content: '';
                }

                .branch-card:hover {
                    transform: translateY(-4px);
                    border-color: #b4c6e2;
                    box-shadow: 0 12px 24px rgba(28, 49, 84, 0.09);
                }

                .branch-card.is-opening {
                    border-color: #4674d5;
                    box-shadow: 0 0 0 3px rgba(36, 86, 199, 0.1);
                }

                .branch-card-content {
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    padding: 20px;
                }

                .branch-card-header {
                    display: flex;
                    align-items: flex-start;
                    gap: 11px;
                }

                .branch-store-icon {
                    display: grid;
                    place-items: center;
                    flex-shrink: 0;
                    width: 43px;
                    height: 43px;
                    border: 1px solid #dce6f6;
                    border-radius: 10px;
                    background: #edf3ff;
                    color: #2456b5;
                    font-size: 20px;
                }

                .branch-store-heading {
                    flex: 1;
                    min-width: 0;
                }

                .branch-store-name {
                    margin: 1px 0 5px;
                    font-size: 15px;
                    font-weight: 750;
                    line-height: 1.45;
                    overflow-wrap: anywhere;
                }

                .branch-store-code {
                    display: inline-block;
                    padding: 3px 7px;
                    border-radius: 4px;
                    background: #f0f3f8;
                    color: #405371;
                    font-size: 11px;
                    font-weight: 650;
                    overflow-wrap: anywhere;
                }

                .branch-status {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    flex-shrink: 0;
                    margin-top: 3px;
                    color: #147044;
                    font-size: 11px;
                    font-weight: 700;
                }

                .branch-status-dot {
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    background: #229b62;
                }

                .branch-details {
                    display: grid;
                    gap: 11px;
                    flex: 1;
                    align-content: start;
                    margin: 18px 0;
                    padding-top: 16px;
                    border-top: 1px solid #e7edf5;
                }

                .branch-detail {
                    display: flex;
                    align-items: flex-start;
                    gap: 9px;
                    color: #405371;
                    line-height: 1.5;
                    overflow-wrap: anywhere;
                }

                .branch-detail i {
                    flex-shrink: 0;
                    color: #526b92;
                }

                .branch-detail-label {
                    display: block;
                    margin-bottom: 1px;
                    color: #617088;
                    font-size: 11px;
                }

                .branch-enter-button {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 9px;
                    width: 100%;
                    min-height: 40px;
                    padding: 9px 13px;
                    border: 1px solid #2456c7;
                    border-radius: 7px;
                    background: #2456c7;
                    color: #fff;
                    font-size: 13px;
                    font-weight: 650;
                    transition: background 160ms, box-shadow 160ms;
                }

                .branch-enter-button:hover:not(:disabled) {
                    background: #1946aa;
                    box-shadow: 0 4px 10px rgba(36, 86, 199, 0.2);
                }

                .branch-enter-button .branch-arrow {
                    transition: transform 160ms;
                }

                .branch-enter-button:hover:not(:disabled) .branch-arrow {
                    transform: translateX(3px);
                }

                .branch-page button:disabled {
                    cursor: wait;
                    opacity: 0.65;
                }

                .branch-page button:focus-visible {
                    outline: 3px solid #80aaff;
                    outline-offset: 3px;
                }

                .branch-empty {
                    grid-column: 1 / -1;
                    padding: 42px 20px;
                    border: 1px dashed #bccce1;
                    border-radius: 12px;
                    background: rgba(255, 255, 255, 0.8);
                    text-align: center;
                }

                .branch-empty > i {
                    display: block;
                    margin-bottom: 12px;
                    color: #526b92;
                    font-size: 30px;
                }

                .branch-empty h3 {
                    margin-bottom: 7px;
                    font-size: 16px;
                    font-weight: 700;
                }

                .branch-empty p {
                    margin: 0;
                    color: var(--branch-secondary);
                }

                .branch-error {
                    display: flex;
                    align-items: center;
                    gap: 9px;
                    margin-bottom: 16px;
                    padding: 12px 15px;
                    border: 1px solid #efc5c5;
                    border-radius: 8px;
                    background: #fff1f1;
                    color: #982c2c;
                }

                .branch-footer {
                    margin-top: 27px;
                    color: #52627a;
                    font-size: 12px;
                    text-align: center;
                }

                @keyframes branch-enter {
                    from {
                        opacity: 0;
                        transform: translateY(12px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @media (max-width: 991px) {
                    .branch-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }

                @media (max-width: 575px) {
                    .branch-shell {
                        padding: 20px 14px;
                    }

                    .branch-topbar {
                        margin-bottom: 20px;
                    }

                    .branch-company {
                        font-size: 14px;
                    }

                    .branch-brand-icon {
                        width: 38px;
                        height: 38px;
                    }

                    .branch-logout {
                        padding: 8px 10px;
                    }

                    .branch-hero {
                        align-items: flex-start;
                        flex-direction: column;
                        gap: 18px;
                        padding: 23px;
                    }

                    .branch-stat {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        min-width: 0;
                        padding: 10px 16px;
                        text-align: left;
                    }

                    .branch-stat-number {
                        font-size: 25px;
                    }

                    .branch-stat-label {
                        margin: 0;
                    }

                    .branch-toolbar {
                        align-items: stretch;
                        flex-direction: column;
                    }

                    .branch-search {
                        width: 100%;
                    }

                    .branch-grid {
                        grid-template-columns: minmax(0, 1fr);
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .branch-page *,
                    .branch-page *::before,
                    .branch-page *::after {
                        animation: none !important;
                        transition: none !important;
                    }

                    .branch-card:hover {
                        transform: none;
                    }
                }
            `}),(0,s.jsx)(`main`,{className:`branch-page`,children:(0,s.jsxs)(`div`,{className:`branch-shell`,children:[(0,s.jsxs)(`header`,{className:`branch-topbar`,children:[(0,s.jsxs)(`div`,{className:`branch-brand`,children:[(0,s.jsx)(`div`,{className:`branch-brand-icon`,children:(0,s.jsx)(`i`,{className:`bi bi-buildings`,"aria-hidden":`true`})}),(0,s.jsxs)(`div`,{children:[(0,s.jsx)(`h2`,{className:`branch-company`,children:e.company_name}),(0,s.jsx)(`div`,{className:`branch-brand-caption`,children:`Branch workspace`})]})]}),(0,s.jsxs)(i,{href:`/logout`,method:`post`,as:`button`,type:`button`,className:`branch-logout`,disabled:g,children:[(0,s.jsx)(`i`,{className:`bi bi-box-arrow-right`,"aria-hidden":`true`}),`Logout`]})]}),(0,s.jsxs)(`section`,{className:`branch-hero`,children:[(0,s.jsxs)(`div`,{className:`branch-hero-content`,children:[(0,s.jsxs)(`div`,{className:`branch-eyebrow`,children:[(0,s.jsx)(`i`,{className:`bi bi-grid-1x2`,"aria-hidden":`true`}),`Your workspace`]}),(0,s.jsx)(`h1`,{children:`Select your branch`}),(0,s.jsx)(`p`,{children:`Choose a branch to continue with billing, inventory and daily operations.`})]}),(0,s.jsxs)(`div`,{className:`branch-stat`,children:[(0,s.jsx)(`div`,{className:`branch-stat-number`,children:t.length}),(0,s.jsxs)(`div`,{className:`branch-stat-label`,children:[`Available `,t.length===1?`branch`:`branches`]})]})]}),(0,s.jsxs)(`div`,{className:`branch-toolbar`,children:[(0,s.jsxs)(`div`,{children:[(0,s.jsx)(`h2`,{className:`branch-section-title`,children:`Available branches`}),(0,s.jsx)(`p`,{className:`branch-section-caption`,role:`status`,"aria-live":`polite`,children:l.trim()?`${m.length} of ${t.length} branches match your search`:`Select Enter Branch to open your workspace`})]}),(0,s.jsxs)(`div`,{className:`branch-search`,children:[(0,s.jsx)(`i`,{className:`bi bi-search`,"aria-hidden":`true`}),(0,s.jsx)(`input`,{type:`search`,value:l,onChange:e=>u(e.target.value),placeholder:`Search branch, code or city...`,"aria-label":`Search branches`}),l&&(0,s.jsx)(`button`,{type:`button`,className:`branch-search-clear`,onClick:()=>u(``),"aria-label":`Clear branch search`,children:(0,s.jsx)(`i`,{className:`bi bi-x-lg`,"aria-hidden":`true`})})]})]}),d&&(0,s.jsxs)(`div`,{className:`branch-error`,role:`alert`,children:[(0,s.jsx)(`i`,{className:`bi bi-exclamation-triangle`,"aria-hidden":`true`}),d]}),(0,s.jsxs)(`div`,{className:`branch-grid`,children:[m.map((e,t)=>{let n=a===e.id,r=[e.city,e.state].filter(Boolean).join(`, `),i=[`#3269cf`,`#168578`,`#7756bb`];return(0,s.jsx)(`article`,{className:`branch-card ${n?`is-opening`:``}`,style:{"--branch-accent":i[t%i.length],"--branch-delay":`${Math.min(t,8)*55}ms`},"aria-busy":n,children:(0,s.jsxs)(`div`,{className:`branch-card-content`,children:[(0,s.jsxs)(`div`,{className:`branch-card-header`,children:[(0,s.jsx)(`div`,{className:`branch-store-icon`,children:(0,s.jsx)(`i`,{className:`bi bi-shop`,"aria-hidden":`true`})}),(0,s.jsxs)(`div`,{className:`branch-store-heading`,children:[(0,s.jsx)(`h3`,{className:`branch-store-name`,children:e.store_name}),e.store_code&&(0,s.jsx)(`span`,{className:`branch-store-code`,children:e.store_code})]}),(0,s.jsxs)(`span`,{className:`branch-status`,children:[(0,s.jsx)(`span`,{className:`branch-status-dot`}),`Active`]})]}),(0,s.jsxs)(`div`,{className:`branch-details`,children:[e.contact_person&&(0,s.jsxs)(`div`,{className:`branch-detail`,children:[(0,s.jsx)(`i`,{className:`bi bi-person`,"aria-hidden":`true`}),(0,s.jsxs)(`div`,{children:[(0,s.jsx)(`span`,{className:`branch-detail-label`,children:`Contact person`}),e.contact_person]})]}),e.phone&&(0,s.jsxs)(`div`,{className:`branch-detail`,children:[(0,s.jsx)(`i`,{className:`bi bi-telephone`,"aria-hidden":`true`}),(0,s.jsxs)(`div`,{children:[(0,s.jsx)(`span`,{className:`branch-detail-label`,children:`Phone`}),e.phone]})]}),r&&(0,s.jsxs)(`div`,{className:`branch-detail`,children:[(0,s.jsx)(`i`,{className:`bi bi-geo-alt`,"aria-hidden":`true`}),(0,s.jsxs)(`div`,{children:[(0,s.jsx)(`span`,{className:`branch-detail-label`,children:`Location`}),r]})]}),!e.contact_person&&!e.phone&&!r&&(0,s.jsxs)(`div`,{className:`branch-detail`,children:[(0,s.jsx)(`i`,{className:`bi bi-info-circle`,"aria-hidden":`true`}),`Contact details have not been added.`]})]}),(0,s.jsx)(`button`,{type:`button`,className:`branch-enter-button`,disabled:g,onClick:()=>h(e.id),"aria-label":n?`Opening ${e.store_name}`:`Enter ${e.store_name}`,children:n?(0,s.jsxs)(s.Fragment,{children:[(0,s.jsx)(`span`,{className:`spinner-border spinner-border-sm`,"aria-hidden":`true`}),`Opening branch...`]}):(0,s.jsxs)(s.Fragment,{children:[`Enter Branch`,(0,s.jsx)(`i`,{className:`bi bi-arrow-right branch-arrow`,"aria-hidden":`true`})]})})]})},e.id)}),m.length===0&&(0,s.jsxs)(`div`,{className:`branch-empty`,children:[(0,s.jsx)(`i`,{className:t.length===0?`bi bi-shop`:`bi bi-search`,"aria-hidden":`true`}),(0,s.jsx)(`h3`,{children:t.length===0?`No branches available`:`No matching branches`}),(0,s.jsx)(`p`,{children:t.length===0?`Contact your administrator for branch access.`:`Try another branch name, code or city.`})]})]}),(0,s.jsxs)(`footer`,{className:`branch-footer`,children:[(0,s.jsx)(`i`,{className:`bi bi-building me-2`,"aria-hidden":`true`}),e.company_name,` · `,`Branch selection`]})]})})]})}export{c as default};