import { useEffect, useId, useRef, useState } from 'react';
import './india-address-fields.css';

const BASE = '/super-admin/company-locations';

function useOptions(path) {
    const [result, setResult] = useState({ data: [], loading: false, error: '' });
    const [retry, setRetry] = useState(0);

    useEffect(() => {
        if (!path) {
            setResult({ data: [], loading: false, error: '' });
            return;
        }
        const controller = new AbortController();
        setResult({ data: [], loading: true, error: '' });
        const timer = setTimeout(async () => {
            try {
                const response = await fetch(`${BASE}/${path}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Location request failed');
                const body = await response.json();
                if (!Array.isArray(body.data))
                    throw new Error('Invalid location response');
                if (!controller.signal.aborted) {
                    setResult({ data: body.data, loading: false, error: '' });
                }
            } catch (error) {
                if (!controller.signal.aborted) {
                    setResult({
                        data: [],
                        loading: false,
                        error: 'Could not load locations.',
                    });
                }
            }
        }, 180);
        return () => {
            clearTimeout(timer);
            controller.abort();
        };
    }, [path, retry]);

    return { ...result, retry: () => setRetry((value) => value + 1) };
}

export function LocationSelect({
    label,
    value,
    options,
    onSelect,
    disabled = false,
    loading = false,
    error = '',
    loadError = '',
    onRetry,
    onQuery,
    allowCustom = false,
    placeholder = 'Search and select',
}) {
    const id = useId();
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [active, setActive] = useState(0);
    const listRef = useRef(null);
    const matches = options.filter((option) =>
        option.name.toLowerCase().includes(query.trim().toLowerCase()),
    );
    const custom =
        allowCustom &&
        query.trim() &&
        !matches.some(
            (option) => option.name.toLowerCase() === query.trim().toLowerCase(),
        );
    const choices = custom
        ? [...matches, { id: null, name: query.trim(), custom: true }]
        : matches;
    const index = Math.min(active, Math.max(choices.length - 1, 0));

    useEffect(() => {
        listRef.current
            ?.querySelector('[aria-selected="true"]')
            ?.scrollIntoView?.({ block: 'nearest' });
    }, [active]);

    const choose = (option) => {
        onSelect(option);
        setQuery('');
        onQuery?.('');
        setOpen(false);
    };

    return (
        <div className="rr-location-field">
            <label htmlFor={id}>
                {label}
                <span aria-hidden="true"> *</span>
            </label>
            <div className={`rr-location-control ${open ? 'is-open' : ''}`}>
                <i
                    className="bi bi-search"
                    aria-hidden="true"
                />
                <input
                    id={id}
                    role="combobox"
                    aria-autocomplete="list"
                    aria-expanded={open}
                    aria-controls={`${id}-list`}
                    aria-activedescendant={
                        open && choices[index] ? `${id}-option-${index}` : undefined
                    }
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? `${id}-error` : undefined}
                    autoComplete="off"
                    disabled={disabled}
                    maxLength={100}
                    placeholder={placeholder}
                    value={open ? query : value}
                    onClick={() => {
                        if (!open) {
                            setOpen(true);
                            setQuery('');
                            setActive(0);
                            onQuery?.('');
                        }
                    }}
                    onFocus={() => {
                        setOpen(true);
                        setQuery('');
                        setActive(0);
                        onQuery?.('');
                    }}
                    onBlur={() => {
                        setOpen(false);
                        setQuery('');
                        onQuery?.('');
                    }}
                    onChange={(event) => {
                        setQuery(event.target.value);
                        setActive(0);
                        setOpen(true);
                        onQuery?.(event.target.value);
                    }}
                    onKeyDown={(event) => {
                        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                            event.preventDefault();
                            setOpen(true);
                            const step = event.key === 'ArrowDown' ? 1 : -1;
                            setActive((current) =>
                                Math.max(
                                    0,
                                    Math.min(choices.length - 1, current + step),
                                ),
                            );
                        } else if (event.key === 'Enter') {
                            event.preventDefault();
                            if (open && choices[index] && !loading)
                                choose(choices[index]);
                            else setOpen(true);
                        } else if (event.key === 'Escape') {
                            event.preventDefault();
                            setOpen(false);
                        }
                    }}
                />
                <i
                    className={`bi ${loading ? 'bi-hourglass-split' : 'bi-chevron-down'}`}
                    aria-hidden="true"
                />
            </div>
            {open && !disabled && (
                <div className="rr-location-dropdown">
                    {loading && (
                        <div
                            role="status"
                            className="rr-location-message"
                        >
                            Loading locations…
                        </div>
                    )}
                    {loadError && (
                        <div
                            className="rr-location-message"
                            role="alert"
                        >
                            {loadError}{' '}
                            <button
                                type="button"
                                onMouseDown={(event) => event.preventDefault()}
                                onClick={onRetry}
                            >
                                Retry
                            </button>
                        </div>
                    )}
                    <ul
                        id={`${id}-list`}
                        role="listbox"
                        aria-label={label}
                        ref={listRef}
                    >
                        {!loading &&
                            choices.map((option, position) => (
                                <li
                                    id={`${id}-option-${position}`}
                                    key={option.id ?? 'custom'}
                                    role="option"
                                    aria-selected={position === index}
                                    onMouseDown={(event) => event.preventDefault()}
                                    onMouseEnter={() => setActive(position)}
                                    onClick={() => choose(option)}
                                >
                                    {option.custom
                                        ? `Use “${option.name}”`
                                        : option.name}
                                </li>
                            ))}
                    </ul>
                    {!loading && !loadError && choices.length === 0 && (
                        <div className="rr-location-message">
                            No matches. Try another spelling.
                        </div>
                    )}
                    {allowCustom && (
                        <small>
                            Search by the beginning of the name, or type your town
                            and choose “Use”.
                        </small>
                    )}
                </div>
            )}
            {error && (
                <div
                    className="rr-location-error"
                    id={`${id}-error`}
                >
                    {error}
                </div>
            )}
        </div>
    );
}

export default function IndiaAddressFields({
    data,
    setData,
    errors,
    disabled = false,
}) {
    const [cityQuery, setCityQuery] = useState('');
    const states = useOptions('states');
    const districts = useOptions(
        data.state_id
            ? `districts?state_id=${encodeURIComponent(data.state_id)}`
            : null,
    );
    const cities = useOptions(
        data.district_id
            ? `cities?district_id=${encodeURIComponent(data.district_id)}&q=${encodeURIComponent(cityQuery)}`
            : null,
    );

    return (
        <div className="col-12 rr-india-address">
            <div className="rr-location-heading">
                <i
                    className="bi bi-geo-alt-fill"
                    aria-hidden="true"
                />
                <div>
                    <strong>Business location</strong>
                    <span>Choose state, district, then your city or town.</span>
                </div>
            </div>
            <div className="rr-location-grid">
                <div className="rr-location-field">
                    <label htmlFor="rr-company-country">Country</label>
                    <input
                        id="rr-company-country"
                        className="rr-location-country"
                        value="India"
                        readOnly
                    />
                    <small>India is selected by default.</small>
                    {errors.country && (
                        <div className="rr-location-error">{errors.country}</div>
                    )}
                </div>
                <LocationSelect
                    label="State / Union Territory"
                    value={data.state}
                    options={states.data}
                    loading={states.loading}
                    loadError={states.error}
                    onRetry={states.retry}
                    disabled={disabled}
                    error={errors.state_id || errors.state}
                    onSelect={(option) => {
                        setCityQuery('');
                        setData((current) => ({
                            ...current,
                            country: 'India',
                            state_id: option.id,
                            state: option.name,
                            district_id: '',
                            district: '',
                            city_id: '',
                            city: '',
                        }));
                    }}
                />
                <LocationSelect
                    label="District"
                    value={data.district}
                    options={districts.data}
                    loading={districts.loading}
                    loadError={districts.error}
                    onRetry={districts.retry}
                    disabled={disabled || !data.state_id}
                    placeholder={
                        data.state_id ? 'Search district' : 'Choose state first'
                    }
                    error={errors.district_id || errors.district}
                    onSelect={(option) => {
                        setCityQuery('');
                        setData((current) => ({
                            ...current,
                            district_id: option.id,
                            district: option.name,
                            city_id: '',
                            city: '',
                        }));
                    }}
                />
                <LocationSelect
                    label="City / Town / Locality"
                    value={data.city}
                    options={cities.data}
                    loading={cities.loading}
                    loadError={cities.error}
                    onRetry={cities.retry}
                    disabled={disabled || !data.district_id}
                    placeholder={
                        data.district_id
                            ? 'Search or enter town'
                            : 'Choose district first'
                    }
                    error={errors.city || errors.city_id}
                    allowCustom
                    onQuery={setCityQuery}
                    onSelect={(option) =>
                        setData((current) => ({
                            ...current,
                            city_id: option.id ?? '',
                            city: option.name,
                        }))
                    }
                />
            </div>
        </div>
    );
}
