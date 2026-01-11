import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import SubTabs from '../components/SubTabs';
import useUrlTab from '../hooks/useUrlTab';

const redirectsTabs = [
    { id: 'redirects', label: 'Redirects' },
    { id: '404-log', label: '404 Log' },
];

const STATUS_CODES = [
    { value: 301, label: '301 Permanent' },
    { value: 302, label: '302 Temporary' },
    { value: 307, label: '307' },
    { value: 410, label: '410 Gone' },
];

const SORT_OPTIONS = [
    { value: 'recent', label: 'Most recent' },
    { value: 'top', label: 'Top hits' },
];

const PER_PAGE_OPTIONS = [25, 50, 100, 200];

const Redirects = () => {
    const [activeTab, setActiveTab] = useUrlTab({ tabs: redirectsTabs, defaultTab: 'redirects' });

    // Redirects state
    const [redirects, setRedirects] = useState([]);
    const [redirectsLoading, setRedirectsLoading] = useState(true);
    const [newSource, setNewSource] = useState('');
    const [newTarget, setNewTarget] = useState('');
    const [newStatusCode, setNewStatusCode] = useState(301);
    const [createLoading, setCreateLoading] = useState(false);
    const [createError, setCreateError] = useState('');

    // Slug suggestions state
    const [suggestions, setSuggestions] = useState([]);
    const [suggestionsLoading, setSuggestionsLoading] = useState(true);

    // 404 Log state
    const [logEntries, setLogEntries] = useState([]);
    const [logLoading, setLogLoading] = useState(true);
    const [logTotal, setLogTotal] = useState(0);
    const [logPage, setLogPage] = useState(1);
    const [logPerPage, setLogPerPage] = useState(50);
    const [logTotalPages, setLogTotalPages] = useState(1);
    const [logSort, setLogSort] = useState('recent');
    const [hideSpam, setHideSpam] = useState(true);
    const [hideImages, setHideImages] = useState(false);
    const [clearingLog, setClearingLog] = useState(false);

    // Fetch redirects
    const fetchRedirects = useCallback(async () => {
        setRedirectsLoading(true);
        try {
            const response = await apiFetch({ path: '/wpseopilot/v2/redirects' });
            if (response.success) {
                setRedirects(response.data);
            }
        } catch (error) {
            console.error('Failed to fetch redirects:', error);
        } finally {
            setRedirectsLoading(false);
        }
    }, []);

    // Fetch slug suggestions
    const fetchSuggestions = useCallback(async () => {
        setSuggestionsLoading(true);
        try {
            const response = await apiFetch({ path: '/wpseopilot/v2/slug-suggestions' });
            if (response.success) {
                setSuggestions(response.data);
            }
        } catch (error) {
            console.error('Failed to fetch suggestions:', error);
        } finally {
            setSuggestionsLoading(false);
        }
    }, []);

    // Fetch 404 log
    const fetchLog = useCallback(async () => {
        setLogLoading(true);
        try {
            const params = new URLSearchParams({
                sort: logSort,
                per_page: logPerPage,
                page: logPage,
                hide_spam: hideSpam ? '1' : '0',
                hide_images: hideImages ? '1' : '0',
            });
            const response = await apiFetch({ path: `/wpseopilot/v2/404-log?${params}` });
            if (response.success) {
                setLogEntries(response.data.items);
                setLogTotal(response.data.total);
                setLogTotalPages(response.data.total_pages);
            }
        } catch (error) {
            console.error('Failed to fetch 404 log:', error);
        } finally {
            setLogLoading(false);
        }
    }, [logSort, logPerPage, logPage, hideSpam, hideImages]);

    // Load data on mount and tab change
    useEffect(() => {
        if (activeTab === 'redirects') {
            fetchRedirects();
            fetchSuggestions();
        } else {
            fetchLog();
        }
    }, [activeTab, fetchRedirects, fetchSuggestions, fetchLog]);

    // Refetch log when filters change
    useEffect(() => {
        if (activeTab === '404-log') {
            fetchLog();
        }
    }, [logSort, logPerPage, logPage, hideSpam, hideImages, fetchLog, activeTab]);

    // Create redirect
    const handleCreateRedirect = async (e) => {
        e.preventDefault();
        setCreateError('');
        setCreateLoading(true);

        try {
            const response = await apiFetch({
                path: '/wpseopilot/v2/redirects',
                method: 'POST',
                data: {
                    source: newSource,
                    target: newTarget,
                    status_code: newStatusCode,
                },
            });

            if (response.success) {
                setRedirects([response.data, ...redirects]);
                setNewSource('');
                setNewTarget('');
                setNewStatusCode(301);
                // Refetch suggestions in case one was auto-removed
                fetchSuggestions();
            } else {
                setCreateError(response.message || 'Failed to create redirect');
            }
        } catch (error) {
            setCreateError(error.message || 'Failed to create redirect');
        } finally {
            setCreateLoading(false);
        }
    };

    // Delete redirect
    const handleDeleteRedirect = async (id) => {
        if (!window.confirm('Are you sure you want to delete this redirect?')) {
            return;
        }

        try {
            await apiFetch({
                path: `/wpseopilot/v2/redirects/${id}`,
                method: 'DELETE',
            });
            setRedirects(redirects.filter(r => r.id !== id));
        } catch (error) {
            console.error('Failed to delete redirect:', error);
        }
    };

    // Apply slug suggestion
    const handleApplySuggestion = async (key) => {
        try {
            const response = await apiFetch({
                path: `/wpseopilot/v2/slug-suggestions/${key}/apply`,
                method: 'POST',
            });
            if (response.success) {
                setSuggestions(suggestions.filter(s => s.key !== key));
                fetchRedirects();
            }
        } catch (error) {
            console.error('Failed to apply suggestion:', error);
        }
    };

    // Dismiss slug suggestion
    const handleDismissSuggestion = async (key) => {
        try {
            await apiFetch({
                path: `/wpseopilot/v2/slug-suggestions/${key}/dismiss`,
                method: 'POST',
            });
            setSuggestions(suggestions.filter(s => s.key !== key));
        } catch (error) {
            console.error('Failed to dismiss suggestion:', error);
        }
    };

    // Use suggestion to prefill form
    const handleUseSuggestion = (suggestion) => {
        setNewSource(suggestion.source);
        setNewTarget(suggestion.target);
        setNewStatusCode(301);
        // Scroll to form
        document.getElementById('redirect-source')?.focus();
    };

    // Create redirect from 404 entry
    const handleCreateFrom404 = (entry) => {
        setNewSource(entry.request_uri);
        setNewTarget('');
        setNewStatusCode(301);
        setActiveTab('redirects');
        setTimeout(() => {
            document.getElementById('redirect-target')?.focus();
        }, 100);
    };

    // Clear 404 log
    const handleClearLog = async () => {
        if (!window.confirm('Are you sure you want to clear the entire 404 log? This cannot be undone.')) {
            return;
        }

        setClearingLog(true);
        try {
            await apiFetch({
                path: '/wpseopilot/v2/404-log',
                method: 'DELETE',
            });
            setLogEntries([]);
            setLogTotal(0);
            setLogTotalPages(1);
            setLogPage(1);
        } catch (error) {
            console.error('Failed to clear log:', error);
        } finally {
            setClearingLog(false);
        }
    };

    // Apply filters
    const handleApplyFilters = (e) => {
        e.preventDefault();
        setLogPage(1);
        fetchLog();
    };

    // Format date
    const formatDate = (dateStr) => {
        if (!dateStr || dateStr === '0000-00-00 00:00:00') return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString() + ', ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    return (
        <div className="page">
            <div className="page-header">
                <div>
                    <h1>Redirects</h1>
                    <p>Manage 301 redirects and monitor 404 errors to improve user experience.</p>
                </div>
                <button type="button" className="button ghost">Import Redirects</button>
            </div>

            <SubTabs tabs={redirectsTabs} activeTab={activeTab} onChange={setActiveTab} ariaLabel="Redirects sections" />

            <section className="panel">
                {activeTab === 'redirects' ? (
                    <>
                        {/* Slug Change Suggestions */}
                        {!suggestionsLoading && suggestions.length > 0 && (
                            <div className="alert-card warning">
                                <div className="alert-header">
                                    <h3>Detected Slug Changes</h3>
                                </div>
                                <p className="muted">The following posts have changed their URL structure. You should probably create redirects to prevent 404 errors.</p>
                                <table className="data-table suggestions-table">
                                    <thead>
                                        <tr>
                                            <th>Old Path</th>
                                            <th>New Target</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {suggestions.map(suggestion => (
                                            <tr key={suggestion.key}>
                                                <td><code>{suggestion.source}</code></td>
                                                <td>
                                                    <a href={suggestion.target} target="_blank" rel="noopener noreferrer">
                                                        {suggestion.target}
                                                    </a>
                                                </td>
                                                <td className="action-buttons">
                                                    <button
                                                        type="button"
                                                        className="button primary small"
                                                        onClick={() => handleApplySuggestion(suggestion.key)}
                                                    >
                                                        Apply
                                                    </button>
                                                    <button
                                                        type="button"
                                                        className="button ghost small"
                                                        onClick={() => handleUseSuggestion(suggestion)}
                                                    >
                                                        Use
                                                    </button>
                                                    <button
                                                        type="button"
                                                        className="link-button danger"
                                                        onClick={() => handleDismissSuggestion(suggestion.key)}
                                                    >
                                                        Dismiss
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {/* Create Redirect Form */}
                        <div className="table-toolbar">
                            <div>
                                <h3>Active Redirects</h3>
                                <p className="muted">Manage your redirect rules.</p>
                            </div>
                        </div>

                        <form onSubmit={handleCreateRedirect} className="redirect-form">
                            <div className="form-row">
                                <div className="form-field">
                                    <label htmlFor="redirect-source">Source Path</label>
                                    <input
                                        type="text"
                                        id="redirect-source"
                                        placeholder="/old-url"
                                        value={newSource}
                                        onChange={(e) => setNewSource(e.target.value)}
                                        required
                                    />
                                </div>
                                <div className="form-field">
                                    <label htmlFor="redirect-target">Target URL</label>
                                    <input
                                        type="url"
                                        id="redirect-target"
                                        placeholder="https://example.com/new-url"
                                        value={newTarget}
                                        onChange={(e) => setNewTarget(e.target.value)}
                                        required
                                    />
                                </div>
                                <div className="form-field narrow">
                                    <label htmlFor="redirect-status">Status</label>
                                    <select
                                        id="redirect-status"
                                        value={newStatusCode}
                                        onChange={(e) => setNewStatusCode(parseInt(e.target.value, 10))}
                                    >
                                        {STATUS_CODES.map(code => (
                                            <option key={code.value} value={code.value}>{code.label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-field button-field">
                                    <button type="submit" className="button primary" disabled={createLoading}>
                                        {createLoading ? 'Adding...' : 'Add Redirect'}
                                    </button>
                                </div>
                            </div>
                            {createError && <p className="form-error">{createError}</p>}
                        </form>

                        {/* Redirects Table */}
                        {redirectsLoading ? (
                            <div className="loading-state">Loading redirects...</div>
                        ) : redirects.length === 0 ? (
                            <div className="empty-state">
                                <p>No redirects configured yet.</p>
                            </div>
                        ) : (
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Target</th>
                                        <th>Status</th>
                                        <th>Hits</th>
                                        <th>Last Hit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {redirects.map(redirect => (
                                        <tr key={redirect.id}>
                                            <td>{redirect.source}</td>
                                            <td>
                                                <a href={redirect.target} target="_blank" rel="noopener noreferrer">
                                                    {redirect.target}
                                                </a>
                                            </td>
                                            <td>{redirect.status_code}</td>
                                            <td>{redirect.hits}</td>
                                            <td>{formatDate(redirect.last_hit)}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    className="link-button danger"
                                                    onClick={() => handleDeleteRedirect(redirect.id)}
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </>
                ) : (
                    <>
                        {/* 404 Log Filters */}
                        <div className="table-toolbar">
                            <div>
                                <h3>404 Error Log</h3>
                                <p className="muted">Monitor broken links and create redirects to fix them.</p>
                            </div>
                            <button
                                type="button"
                                className="button ghost"
                                onClick={handleClearLog}
                                disabled={clearingLog || logEntries.length === 0}
                            >
                                {clearingLog ? 'Clearing...' : 'Clear Log'}
                            </button>
                        </div>

                        <form onSubmit={handleApplyFilters} className="filter-form">
                            <div className="filter-row">
                                <label className="filter-field">
                                    <span>Sort by</span>
                                    <select
                                        value={logSort}
                                        onChange={(e) => {
                                            setLogSort(e.target.value);
                                            setLogPage(1);
                                        }}
                                    >
                                        {SORT_OPTIONS.map(opt => (
                                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                                        ))}
                                    </select>
                                </label>
                                <label className="filter-field">
                                    <span>Rows per page</span>
                                    <select
                                        value={logPerPage}
                                        onChange={(e) => {
                                            setLogPerPage(parseInt(e.target.value, 10));
                                            setLogPage(1);
                                        }}
                                    >
                                        {PER_PAGE_OPTIONS.map(opt => (
                                            <option key={opt} value={opt}>{opt}</option>
                                        ))}
                                    </select>
                                </label>
                                <label className="filter-checkbox">
                                    <input
                                        type="checkbox"
                                        checked={hideSpam}
                                        onChange={(e) => {
                                            setHideSpam(e.target.checked);
                                            setLogPage(1);
                                        }}
                                    />
                                    <span>Hide spammy extensions</span>
                                </label>
                                <label className="filter-checkbox">
                                    <input
                                        type="checkbox"
                                        checked={hideImages}
                                        onChange={(e) => {
                                            setHideImages(e.target.checked);
                                            setLogPage(1);
                                        }}
                                    />
                                    <span>Hide image extensions</span>
                                </label>
                            </div>
                        </form>

                        {/* Log Stats */}
                        {!logLoading && (
                            <p className="log-stats">
                                {logTotal.toLocaleString()} entries logged. Page {logPage} of {logTotalPages}.
                            </p>
                        )}

                        {/* 404 Log Table */}
                        {logLoading ? (
                            <div className="loading-state">Loading 404 log...</div>
                        ) : logEntries.length === 0 ? (
                            <div className="empty-state">
                                <p>No 404s logged yet.</p>
                            </div>
                        ) : (
                            <>
                                <table className="data-table">
                                    <thead>
                                        <tr>
                                            <th>Target URL</th>
                                            <th>Hits</th>
                                            <th>Date &amp; Time</th>
                                            <th>User Device</th>
                                            <th>Quick Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {logEntries.map(entry => (
                                            <tr key={entry.id}>
                                                <td>
                                                    {entry.request_uri}
                                                    {entry.redirect_exists && (
                                                        <span className="badge redirect-exists">Redirect exists</span>
                                                    )}
                                                </td>
                                                <td>{entry.hits}</td>
                                                <td>{formatDate(entry.last_seen)}</td>
                                                <td>{entry.device_label}</td>
                                                <td>
                                                    {!entry.redirect_exists && (
                                                        <button
                                                            type="button"
                                                            className="button small"
                                                            onClick={() => handleCreateFrom404(entry)}
                                                        >
                                                            Create redirect
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>

                                {/* Pagination */}
                                {logTotalPages > 1 && (
                                    <div className="pagination">
                                        <span className="pagination-info">
                                            {logTotal.toLocaleString()} {logTotal === 1 ? 'item' : 'items'}
                                        </span>
                                        <div className="pagination-links">
                                            <button
                                                type="button"
                                                className="pagination-btn"
                                                disabled={logPage <= 1}
                                                onClick={() => setLogPage(logPage - 1)}
                                            >
                                                &lsaquo; Previous
                                            </button>
                                            <span className="pagination-current">
                                                {logPage} of {logTotalPages}
                                            </span>
                                            <button
                                                type="button"
                                                className="pagination-btn"
                                                disabled={logPage >= logTotalPages}
                                                onClick={() => setLogPage(logPage + 1)}
                                            >
                                                Next &rsaquo;
                                            </button>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </>
                )}
            </section>
        </div>
    );
};

export default Redirects;
