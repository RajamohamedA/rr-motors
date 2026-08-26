import {
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';

export default function SearchableSelect({
    value,
    options = [],
    onChange,
    placeholder = 'Select...',
    searchPlaceholder = 'Search...',
    allLabel = 'All',
}) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');

    const containerRef = useRef(null);
    const inputRef = useRef(null);

    const selectedOption = options.find(
        (option) => String(option.value) === String(value)
    );

    const filteredOptions = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return options;
        }

        return options.filter((option) =>
            String(option.label)
                .toLowerCase()
                .includes(term)
        );
    }, [options, search]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target)
            ) {
                setOpen(false);
                setSearch('');
            }
        };

        document.addEventListener(
            'mousedown',
            handleClickOutside
        );

        return () => {
            document.removeEventListener(
                'mousedown',
                handleClickOutside
            );
        };
    }, []);

    useEffect(() => {
        if (open) {
            setTimeout(() => {
                inputRef.current?.focus();
            }, 0);
        }
    }, [open]);

    const selectOption = (newValue) => {
        onChange(newValue);

        setOpen(false);
        setSearch('');
    };

    return (
        <div
            className="rr-search-select"
            ref={containerRef}
        >
            {/* Selected value */}

            <button
                type="button"
                className={`rr-search-select-trigger ${open ? 'is-open' : ''
                    }`}
                onClick={() => setOpen(!open)}
            >
                <span>
                    {value === 'all'
                        ? allLabel
                        : selectedOption?.label ?? placeholder}
                </span>

                <i
                    className={`bi ${open
                            ? 'bi-chevron-up'
                            : 'bi-chevron-down'
                        }`}
                ></i>
            </button>

            {/* Dropdown */}

            {open && (
                <div className="rr-search-select-menu">

                    {/* Search */}

                    <div className="rr-search-select-search">

                        <i className="bi bi-search"></i>

                        <input
                            ref={inputRef}
                            type="text"
                            value={search}
                            onChange={(event) =>
                                setSearch(event.target.value)
                            }
                            placeholder={searchPlaceholder}
                        />

                    </div>

                    {/* Options */}

                    <div className="rr-search-select-options">

                        <button
                            type="button"
                            className={`rr-search-select-option ${value === 'all'
                                    ? 'selected'
                                    : ''
                                }`}
                            onClick={() =>
                                selectOption('all')
                            }
                        >
                            <span>{allLabel}</span>

                            {value === 'all' && (
                                <i className="bi bi-check-lg"></i>
                            )}
                        </button>

                        {filteredOptions.map((option) => {

                            const selected =
                                String(value) ===
                                String(option.value);

                            return (
                                <button
                                    key={option.value}
                                    type="button"
                                    className={`rr-search-select-option ${selected
                                            ? 'selected'
                                            : ''
                                        }`}
                                    onClick={() =>
                                        selectOption(
                                            String(option.value)
                                        )
                                    }
                                >
                                    <span>
                                        {option.label}
                                    </span>

                                    {selected && (
                                        <i className="bi bi-check-lg"></i>
                                    )}
                                </button>
                            );
                        })}

                        {filteredOptions.length === 0 && (
                            <div className="rr-search-select-empty">
                                No companies found
                            </div>
                        )}

                    </div>

                </div>
            )}
        </div>
    );
}