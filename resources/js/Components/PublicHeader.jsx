import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Edit branding and navigation here once for every public page.
const BRAND = {
    icon: '/images/rr-erp-icon.png?v=2',
    wordmark: '/images/rr-erp-wordmark.png',
    name: 'RR ERP',
    caption: 'Business Management Platform',
};
const NAVIGATION = [
    { label: 'Features', href: '/#features' },
    { label: 'Modules', href: '/#modules' },
    { label: 'Multi Branch', href: '/#multi-branch' },
    { label: 'Security', href: '/#security' },
];
const ACCOUNT_ACTIONS = {
    login: {
        prompt: 'New to RR ERP?',
        href: '/register-business',
        label: 'Register Business',
    },
    register: {
        prompt: 'Already have an account?',
        href: '/login',
        label: 'Sign In',
    },
    forgot: { prompt: 'Remember your password?', href: '/login', label: 'Sign In' },
};

export function PublicBrand({ footer = false }) {
    const [iconFailed, setIconFailed] = useState(false);
    const [wordmarkFailed, setWordmarkFailed] = useState(false);
    return (
        <span
            className={`rr-public-brand ${footer ? 'rr-public-brand--footer' : ''}`}
        >
            <span
                className="rr-public-brand-icon"
                aria-hidden="true"
            >
                {iconFailed ? (
                    <span>RR</span>
                ) : (
                    <img
                        src={BRAND.icon}
                        alt=""
                        width="42"
                        height="42"
                        decoding="async"
                        onError={() => setIconFailed(true)}
                    />
                )}
            </span>
            <span className="rr-public-brand-copy">
                {wordmarkFailed ? (
                    <strong className="rr-public-brand-fallback">
                        RR <span>ERP</span>
                    </strong>
                ) : (
                    <img
                        className="rr-public-brand-wordmark"
                        src={BRAND.wordmark}
                        alt={BRAND.name}
                        width="160"
                        height="31"
                        decoding="async"
                        onError={() => setWordmarkFailed(true)}
                    />
                )}
                <span className="rr-public-brand-caption">{BRAND.caption}</span>
            </span>
        </span>
    );
}

export default function PublicHeader({ page = 'welcome' }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const welcome = page === 'welcome';
    const accountAction = ACCOUNT_ACTIONS[page] ?? ACCOUNT_ACTIONS.login;
    useEffect(() => {
        const closeOnEscape = (event) => {
            if (event.key === 'Escape') setMenuOpen(false);
        };
        window.addEventListener('keydown', closeOnEscape);
        return () => window.removeEventListener('keydown', closeOnEscape);
    }, []);
    const closeMenu = () => setMenuOpen(false);
    return (
        <>
            <style>{PUBLIC_HEADER_STYLES}</style>
            <header className="rr-public-header">
                <div className="rr-public-header-inner">
                    <Link
                        href="/"
                        className="rr-public-home-link"
                        onClick={closeMenu}
                    >
                        <PublicBrand />
                    </Link>
                    {welcome && (
                        <nav
                            className="rr-public-desktop-nav"
                            aria-label="Main navigation"
                        >
                            {NAVIGATION.map((item) => (
                                <a
                                    key={item.href}
                                    href={item.href}
                                >
                                    {item.label}
                                </a>
                            ))}
                        </nav>
                    )}
                    <div className="rr-public-actions">
                        {welcome ? (
                            <>
                                <Link
                                    href="/login"
                                    className="rr-public-button rr-public-button--quiet"
                                >
                                    Sign In
                                </Link>
                                <Link
                                    href="/register-business"
                                    className="rr-public-button rr-public-button--primary"
                                >
                                    Start Your Business
                                    <i
                                        className="bi bi-arrow-right"
                                        aria-hidden="true"
                                    />
                                </Link>
                                <button
                                    type="button"
                                    className="rr-public-menu-toggle"
                                    aria-label={
                                        menuOpen
                                            ? 'Close navigation'
                                            : 'Open navigation'
                                    }
                                    aria-expanded={menuOpen}
                                    aria-controls="rr-public-mobile-nav"
                                    onClick={() => setMenuOpen((open) => !open)}
                                >
                                    <i
                                        className={
                                            menuOpen ? 'bi bi-x-lg' : 'bi bi-list'
                                        }
                                        aria-hidden="true"
                                    />
                                </button>
                            </>
                        ) : (
                            <>
                                <span className="rr-public-account-prompt">
                                    {accountAction.prompt}
                                </span>
                                <Link
                                    href={accountAction.href}
                                    className="rr-public-button rr-public-button--primary"
                                >
                                    {accountAction.label}
                                    <i
                                        className="bi bi-arrow-right"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </>
                        )}
                    </div>
                </div>
                {welcome && menuOpen && (
                    <nav
                        id="rr-public-mobile-nav"
                        className="rr-public-mobile-nav"
                        aria-label="Mobile navigation"
                    >
                        {NAVIGATION.map((item) => (
                            <a
                                key={item.href}
                                href={item.href}
                                onClick={closeMenu}
                            >
                                {item.label}
                                <i
                                    className="bi bi-chevron-right"
                                    aria-hidden="true"
                                />
                            </a>
                        ))}
                        <Link
                            href="/login"
                            onClick={closeMenu}
                        >
                            Sign In
                        </Link>
                        <Link
                            href="/register-business"
                            onClick={closeMenu}
                        >
                            Start Your Business
                        </Link>
                    </nav>
                )}
            </header>
        </>
    );
}

// Kept with the shared component so header design changes need only this file.
const PUBLIC_HEADER_STYLES = `
.rr-public-header,.rr-public-header *,.rr-public-brand,.rr-public-brand *{box-sizing:border-box}
.rr-public-header{position:fixed;inset:0 0 auto;z-index:1200;min-height:74px;border-bottom:1px solid #ffffff16;background:#0d162bf7;box-shadow:0 3px 18px #0b183718;font-family:inherit}
.rr-public-header-inner{display:flex;align-items:center;justify-content:space-between;gap:20px;min-height:74px;width:100%;max-width:1440px;margin:0 auto;padding:8px 30px}
.rr-public-home-link{display:inline-flex;flex:0 0 auto;min-width:0;text-decoration:none;color:#fff}
.rr-public-brand{display:inline-flex;align-items:center;gap:9px;max-width:100%;flex-shrink:0;color:#fff}
.rr-public-brand-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border:1px solid #3a4e77;border-radius:11px;background:#1a2b4a;color:#9bb5ff;font-size:16px;font-weight:800}
.rr-public-brand-icon img{display:block;width:42px;height:42px;object-fit:contain}
.rr-public-brand-copy{display:flex;flex-direction:column;align-items:flex-start;gap:1px;min-width:0}
.rr-public-brand-wordmark{display:block;width:160px;height:31px;object-fit:cover;object-position:center}
.rr-public-brand-caption{font-size:10px;line-height:1.4;font-weight:500;color:#c0cfe5;letter-spacing:.1px;white-space:nowrap}
.rr-public-brand-fallback{font-size:23px;line-height:31px;color:#fff;letter-spacing:1px}.rr-public-brand-fallback span{color:#568aff}
.rr-public-brand--footer{padding:9px 12px;border:1px solid #34456a;border-radius:13px;background:#101c35}
.rr-public-desktop-nav{display:flex;align-items:center;justify-content:center;gap:24px;margin-left:auto}
.rr-public-desktop-nav a{color:#c9d6e9;text-decoration:none;font-size:12px;font-weight:600;white-space:nowrap;transition:color .18s}
.rr-public-desktop-nav a:hover{color:#fff}
.rr-public-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}
.rr-public-account-prompt{font-size:12px;color:#c0cfe5}
.rr-public-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:38px;padding:9px 15px;border:1px solid transparent;border-radius:9px;font-size:12px;font-weight:650;line-height:1.3;text-decoration:none;white-space:nowrap;cursor:pointer;transition:background .18s,box-shadow .18s}
.rr-public-button--primary{background:linear-gradient(120deg,#2563eb,#4f46e5);border-color:#6184f3;color:#fff;box-shadow:0 3px 12px #2563eb25}
.rr-public-button--primary:hover{background:#315ddd;color:#fff;box-shadow:0 5px 17px #2563eb40}
.rr-public-button--quiet{background:#ffffff09;border-color:#ffffff28;color:#e5edfa}.rr-public-button--quiet:hover{background:#ffffff19;color:#fff}
.rr-public-menu-toggle{display:none;align-items:center;justify-content:center;width:38px;height:38px;border:1px solid #ffffff30;border-radius:9px;background:#ffffff0d;color:#fff;font-size:19px;cursor:pointer}
.rr-public-mobile-nav{display:grid;gap:4px;padding:10px 18px 16px;border-top:1px solid #ffffff15;background:#101c33}
.rr-public-mobile-nav a{display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:8px;color:#e1eafa;text-decoration:none;font-size:13px}.rr-public-mobile-nav a:hover{background:#ffffff0c;color:#fff}
.rr-public-header :is(a,button):focus-visible{outline:2px solid #9db7ff;outline-offset:3px}
@media(max-width:1100px){.rr-public-desktop-nav{display:none}.rr-public-menu-toggle{display:flex}.rr-public-header-inner{padding-inline:20px}}
@media(min-width:1101px){.rr-public-mobile-nav{display:none}}
@media(max-width:600px){.rr-public-header-inner{padding-inline:12px;gap:10px}.rr-public-brand-icon{width:37px;height:37px;flex-basis:37px}.rr-public-brand-icon img{width:35px;height:35px}.rr-public-brand-wordmark{width:132px;height:28px}.rr-public-brand-caption{font-size:8px}.rr-public-brand{gap:6px}.rr-public-account-prompt{display:none}.rr-public-button{font-size:11px;padding:8px 10px;min-height:35px}.rr-public-actions{gap:7px}.rr-public-header .rr-public-button--primary i{display:none}}
@media(max-width:440px){.rr-public-header .rr-public-button--quiet{display:none}.rr-public-header .rr-public-actions>.rr-public-button--primary{max-width:118px;white-space:normal;text-align:center;font-size:10px}.rr-public-header-inner{gap:7px}.rr-public-header .rr-public-brand-wordmark{width:115px}.rr-public-header .rr-public-brand-caption{font-size:7.5px}}
@media(prefers-reduced-motion:reduce){.rr-public-header *{transition:none!important;animation:none!important}}
`;
