import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import SubTabs from '../components/SubTabs';
import useUrlTab from '../hooks/useUrlTab';

const sitemapTabs = [
    { id: 'xml-sitemap', label: 'XML Sitemap' },
    { id: 'llm-txt', label: 'LLM.txt' },
];

const SCHEDULE_OPTIONS = [
    { value: '', label: 'Manual only' },
    { value: 'hourly', label: 'Hourly' },
    { value: 'twicedaily', label: 'Twice Daily' },
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
];

const Sitemap = () => {
    const [activeTab, setActiveTab] = useUrlTab({ tabs: sitemapTabs, defaultTab: 'xml-sitemap' });

    // Loading states
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [regenerating, setRegenerating] = useState(false);

    // Sitemap settings
    const [settings, setSettings] = useState({
        max_urls: 1000,
        enable_index: '1',
        dynamic_generation: '1',
        schedule_updates: '',
        post_types: [],
        taxonomies: [],
        include_author_pages: '0',
        include_date_archives: '0',
        exclude_images: '0',
        enable_rss: '0',
        enable_google_news: '0',
        google_news_name: '',
        google_news_post_types: [],
        additional_pages: [],
        site_url: '',
        sitemap_url: '',
        rss_sitemap_url: '',
        news_sitemap_url: '',
    });

    // LLM settings
    const [llmSettings, setLlmSettings] = useState({
        enable_llm_txt: '0',
        llm_txt_title: '',
        llm_txt_description: '',
        llm_txt_posts_per_type: 50,
        llm_txt_include_excerpt: '1',
        llm_txt_url: '',
    });

    // Available post types and taxonomies
    const [postTypes, setPostTypes] = useState([]);
    const [taxonomies, setTaxonomies] = useState([]);

    // Stats
    const [stats, setStats] = useState({
        total_urls: 0,
        last_regenerated: null,
    });

    // Fetch all data
    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            const [settingsRes, llmRes, postTypesRes, taxonomiesRes, statsRes] = await Promise.all([
                apiFetch({ path: '/wpseopilot/v2/sitemap/settings' }),
                apiFetch({ path: '/wpseopilot/v2/sitemap/llm-settings' }),
                apiFetch({ path: '/wpseopilot/v2/sitemap/post-types' }),
                apiFetch({ path: '/wpseopilot/v2/sitemap/taxonomies' }),
                apiFetch({ path: '/wpseopilot/v2/sitemap/stats' }),
            ]);

            if (settingsRes.success) setSettings(settingsRes.data);
            if (llmRes.success) setLlmSettings(llmRes.data);
            if (postTypesRes.success) setPostTypes(postTypesRes.data);
            if (taxonomiesRes.success) setTaxonomies(taxonomiesRes.data);
            if (statsRes.success) setStats(statsRes.data);
        } catch (error) {
            console.error('Failed to fetch sitemap data:', error);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    // Save sitemap settings
    const handleSaveSettings = async () => {
        setSaving(true);
        try {
            await apiFetch({
                path: '/wpseopilot/v2/sitemap/settings',
                method: 'POST',
                data: settings,
            });
            // Refetch stats after saving
            const statsRes = await apiFetch({ path: '/wpseopilot/v2/sitemap/stats' });
            if (statsRes.success) setStats(statsRes.data);
        } catch (error) {
            console.error('Failed to save settings:', error);
        } finally {
            setSaving(false);
        }
    };

    // Save LLM settings
    const handleSaveLlmSettings = async () => {
        setSaving(true);
        try {
            await apiFetch({
                path: '/wpseopilot/v2/sitemap/llm-settings',
                method: 'POST',
                data: llmSettings,
            });
        } catch (error) {
            console.error('Failed to save LLM settings:', error);
        } finally {
            setSaving(false);
        }
    };

    // Regenerate sitemap
    const handleRegenerate = async () => {
        setRegenerating(true);
        try {
            const res = await apiFetch({
                path: '/wpseopilot/v2/sitemap/regenerate',
                method: 'POST',
            });
            if (res.success) {
                setStats(prev => ({
                    ...prev,
                    last_regenerated: res.data.regenerated_at,
                }));
            }
        } catch (error) {
            console.error('Failed to regenerate sitemap:', error);
        } finally {
            setRegenerating(false);
        }
    };

    // Toggle post type selection
    const togglePostType = (name) => {
        setSettings(prev => {
            const current = Array.isArray(prev.post_types) ? prev.post_types : [];
            const updated = current.includes(name)
                ? current.filter(pt => pt !== name)
                : [...current, name];
            return { ...prev, post_types: updated };
        });
    };

    // Toggle taxonomy selection
    const toggleTaxonomy = (name) => {
        setSettings(prev => {
            const current = Array.isArray(prev.taxonomies) ? prev.taxonomies : [];
            const updated = current.includes(name)
                ? current.filter(t => t !== name)
                : [...current, name];
            return { ...prev, taxonomies: updated };
        });
    };

    // Toggle Google News post type
    const toggleNewsPostType = (name) => {
        setSettings(prev => {
            const current = Array.isArray(prev.google_news_post_types) ? prev.google_news_post_types : [];
            const updated = current.includes(name)
                ? current.filter(pt => pt !== name)
                : [...current, name];
            return { ...prev, google_news_post_types: updated };
        });
    };

    // Add additional page
    const addAdditionalPage = () => {
        setSettings(prev => ({
            ...prev,
            additional_pages: [...(prev.additional_pages || []), { url: '', priority: '0.5' }],
        }));
    };

    // Update additional page
    const updateAdditionalPage = (index, field, value) => {
        setSettings(prev => {
            const pages = [...(prev.additional_pages || [])];
            pages[index] = { ...pages[index], [field]: value };
            return { ...prev, additional_pages: pages };
        });
    };

    // Remove additional page
    const removeAdditionalPage = (index) => {
        setSettings(prev => ({
            ...prev,
            additional_pages: (prev.additional_pages || []).filter((_, i) => i !== index),
        }));
    };

    // Format date
    const formatDate = (dateStr) => {
        if (!dateStr) return 'Never';
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const hours = Math.floor(diff / (1000 * 60 * 60));

        if (hours < 1) return 'Just now';
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        return date.toLocaleDateString();
    };

    if (loading) {
        return (
            <div className="page">
                <div className="page-header">
                    <div>
                        <h1>Sitemap</h1>
                        <p>Configure XML sitemap generation and LLM.txt settings.</p>
                    </div>
                </div>
                <div className="loading-state">Loading sitemap settings...</div>
            </div>
        );
    }

    return (
        <div className="page">
            <div className="page-header">
                <div>
                    <h1>Sitemap</h1>
                    <p>Configure XML sitemap generation and LLM.txt settings.</p>
                </div>
                <div className="header-actions">
                    <a
                        href={settings.sitemap_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="button ghost"
                    >
                        View Sitemap
                    </a>
                    {llmSettings.enable_llm_txt === '1' && (
                        <a
                            href={llmSettings.llm_txt_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="button ghost"
                        >
                            Open llm.txt
                        </a>
                    )}
                </div>
            </div>

            <SubTabs tabs={sitemapTabs} activeTab={activeTab} onChange={setActiveTab} ariaLabel="Sitemap sections" />

            {activeTab === 'xml-sitemap' ? (
                <div className="page-body two-column">
                    <div className="main-column">
                        {/* XML Sitemap Configuration */}
                        <section className="panel">
                            <h3>XML Sitemap Configuration</h3>
                            <p className="muted">Control which content appears in your XML sitemap and how it is organized.</p>

                            <div className="settings-form">
                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Automatic Updates</label>
                                        <p className="settings-help">Automatically regenerate sitemap on a schedule.</p>
                                    </div>
                                    <div className="settings-control">
                                        <select
                                            value={settings.schedule_updates}
                                            onChange={(e) => setSettings(prev => ({ ...prev, schedule_updates: e.target.value }))}
                                        >
                                            {SCHEDULE_OPTIONS.map(opt => (
                                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Max URLs Per Page</label>
                                        <p className="settings-help">Maximum number of URLs per sitemap page (recommended: 1000).</p>
                                    </div>
                                    <div className="settings-control">
                                        <input
                                            type="number"
                                            value={settings.max_urls}
                                            onChange={(e) => setSettings(prev => ({ ...prev, max_urls: parseInt(e.target.value, 10) || 1000 }))}
                                            min="1"
                                            max="50000"
                                            style={{ width: '120px' }}
                                        />
                                    </div>
                                </div>

                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Options</label>
                                    </div>
                                    <div className="settings-control">
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.enable_index === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, enable_index: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Enable sitemap indexes for better organization</span>
                                        </label>
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.dynamic_generation === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, dynamic_generation: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Dynamically generate sitemap on-demand</span>
                                        </label>
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.exclude_images === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, exclude_images: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Exclude images from sitemap entries</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Content Types */}
                        <section className="panel">
                            <h3>Content Types</h3>
                            <p className="muted">Select which post types and taxonomies should be included in your sitemap.</p>

                            <div className="settings-form">
                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label><strong>Post Types</strong></label>
                                    </div>
                                    <div className="settings-control">
                                        <div className="checkbox-grid">
                                            {postTypes.map(pt => (
                                                <label key={pt.name} className="checkbox-row">
                                                    <input
                                                        type="checkbox"
                                                        checked={(settings.post_types || []).includes(pt.name)}
                                                        onChange={() => togglePostType(pt.name)}
                                                    />
                                                    <span>{pt.label} ({pt.count})</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label><strong>Taxonomies</strong></label>
                                    </div>
                                    <div className="settings-control">
                                        <div className="checkbox-grid">
                                            {taxonomies.map(tax => (
                                                <label key={tax.name} className="checkbox-row">
                                                    <input
                                                        type="checkbox"
                                                        checked={(settings.taxonomies || []).includes(tax.name)}
                                                        onChange={() => toggleTaxonomy(tax.name)}
                                                    />
                                                    <span>{tax.label} ({tax.count})</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Archives</label>
                                    </div>
                                    <div className="settings-control">
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.include_author_pages === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, include_author_pages: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Include author archive pages</span>
                                        </label>
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.include_date_archives === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, include_date_archives: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Include date archive pages</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Additional Sitemaps */}
                        <section className="panel">
                            <h3>Additional Sitemaps</h3>
                            <p className="muted">Enable specialized sitemaps for RSS feeds and Google News.</p>

                            <div className="settings-form">
                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>RSS Sitemap</label>
                                    </div>
                                    <div className="settings-control">
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.enable_rss === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, enable_rss: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span>Generate RSS sitemap with latest 50 posts</span>
                                        </label>
                                    </div>
                                </div>

                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Google News</label>
                                    </div>
                                    <div className="settings-control">
                                        <label className="checkbox-row">
                                            <input
                                                type="checkbox"
                                                checked={settings.enable_google_news === '1'}
                                                onChange={(e) => setSettings(prev => ({ ...prev, enable_google_news: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span><strong>Enable Google News sitemap</strong></span>
                                        </label>

                                        {settings.enable_google_news === '1' && (
                                            <>
                                                <div style={{ marginTop: '12px' }}>
                                                    <input
                                                        type="text"
                                                        value={settings.google_news_name}
                                                        onChange={(e) => setSettings(prev => ({ ...prev, google_news_name: e.target.value }))}
                                                        placeholder="Publication Name"
                                                        style={{ width: '100%' }}
                                                    />
                                                    <p className="settings-help">The name of your publication for Google News.</p>
                                                </div>

                                                <div style={{ marginTop: '12px' }}>
                                                    <p className="settings-help">Post types to include:</p>
                                                    <div className="checkbox-grid">
                                                        {postTypes.map(pt => (
                                                            <label key={pt.name} className="checkbox-row">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={(settings.google_news_post_types || []).includes(pt.name)}
                                                                    onChange={() => toggleNewsPostType(pt.name)}
                                                                />
                                                                <span>{pt.label}</span>
                                                            </label>
                                                        ))}
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Additional Pages */}
                        <section className="panel">
                            <h3>Additional Pages</h3>
                            <p className="muted">Add custom URLs to your sitemap that are not managed by WordPress.</p>

                            <div className="additional-pages-list">
                                {(settings.additional_pages || []).map((page, index) => (
                                    <div key={index} className="additional-page-row">
                                        <input
                                            type="url"
                                            value={page.url}
                                            onChange={(e) => updateAdditionalPage(index, 'url', e.target.value)}
                                            placeholder="https://example.com/page"
                                            className="page-url-input"
                                        />
                                        <input
                                            type="text"
                                            value={page.priority}
                                            onChange={(e) => updateAdditionalPage(index, 'priority', e.target.value)}
                                            placeholder="0.5"
                                            className="page-priority-input"
                                        />
                                        <button
                                            type="button"
                                            className="button ghost small"
                                            onClick={() => removeAdditionalPage(index)}
                                        >
                                            Remove
                                        </button>
                                    </div>
                                ))}
                            </div>

                            <button type="button" className="button ghost" onClick={addAdditionalPage}>
                                Add Page
                            </button>
                            <p className="settings-help" style={{ marginTop: '8px' }}>
                                Add custom URLs with their priority (0.0 to 1.0).
                            </p>
                        </section>

                        {/* Save Button */}
                        <div className="form-actions">
                            <button
                                type="button"
                                className="button primary"
                                onClick={handleSaveSettings}
                                disabled={saving}
                            >
                                {saving ? 'Saving...' : 'Save Changes'}
                            </button>
                            <button
                                type="button"
                                className="button ghost"
                                onClick={handleRegenerate}
                                disabled={regenerating}
                            >
                                {regenerating ? 'Regenerating...' : 'Regenerate Now'}
                            </button>
                        </div>
                    </div>

                    {/* Sidebar */}
                    <aside className="side-panel">
                        <div className="side-card highlight">
                            <h3>Your Sitemaps</h3>
                            <p className="muted">Access your generated sitemaps below.</p>

                            <div className="sitemap-links">
                                <div className="sitemap-link-item">
                                    <strong>Main Index:</strong>
                                    <a href={settings.sitemap_url} target="_blank" rel="noopener noreferrer">
                                        {settings.sitemap_url}
                                    </a>
                                </div>
                                {settings.enable_rss === '1' && (
                                    <div className="sitemap-link-item">
                                        <strong>RSS:</strong>
                                        <a href={settings.rss_sitemap_url} target="_blank" rel="noopener noreferrer">
                                            {settings.rss_sitemap_url}
                                        </a>
                                    </div>
                                )}
                                {settings.enable_google_news === '1' && (
                                    <div className="sitemap-link-item">
                                        <strong>Google News:</strong>
                                        <a href={settings.news_sitemap_url} target="_blank" rel="noopener noreferrer">
                                            {settings.news_sitemap_url}
                                        </a>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="side-card">
                            <h3>Sitemap Stats</h3>
                            <div className="stats-list">
                                <div className="stat-item">
                                    <span className="muted">Total URLs:</span>
                                    <span className="stat-value">{stats.total_urls.toLocaleString()}</span>
                                </div>
                                <div className="stat-item">
                                    <span className="muted">Last Regenerated:</span>
                                    <span className="stat-value">{formatDate(stats.last_regenerated)}</span>
                                </div>
                            </div>
                        </div>

                        <div className="side-card">
                            <h3>Tip</h3>
                            <p className="muted">
                                Submit your sitemap to Google Search Console and Bing Webmaster Tools for better indexing.
                            </p>
                        </div>
                    </aside>
                </div>
            ) : (
                <div className="page-body two-column">
                    <div className="main-column">
                        {/* LLM.txt Introduction */}
                        <section className="panel">
                            <h3>LLM.txt</h3>
                            <p className="muted">
                                The llm.txt is a specialized file designed to help AI engines (such as language models)
                                discover the content on your site more easily. Similar to how XML sitemaps assist search
                                engines, the llm.txt file guides AI crawlers by providing important details about the
                                available site content.{' '}
                                <a href="https://llmstxt.org/" target="_blank" rel="noopener noreferrer">
                                    Learn More
                                </a>
                            </p>

                            <div className="settings-form">
                                <div className="settings-row">
                                    <div className="settings-label">
                                        <label>Enable llm.txt</label>
                                    </div>
                                    <div className="settings-control">
                                        <label className="toggle">
                                            <input
                                                type="checkbox"
                                                checked={llmSettings.enable_llm_txt === '1'}
                                                onChange={(e) => setLlmSettings(prev => ({ ...prev, enable_llm_txt: e.target.checked ? '1' : '0' }))}
                                            />
                                            <span className="toggle-track" />
                                            <span className="toggle-text">
                                                {llmSettings.enable_llm_txt === '1' ? 'Enabled' : 'Disabled'}
                                            </span>
                                        </label>
                                        {llmSettings.enable_llm_txt === '1' && (
                                            <div style={{ marginTop: '12px' }}>
                                                <a
                                                    href={llmSettings.llm_txt_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="button ghost"
                                                >
                                                    Open llm.txt
                                                </a>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </section>

                        {llmSettings.enable_llm_txt === '1' && (
                            <>
                                {/* LLM.txt Settings */}
                                <section className="panel">
                                    <h3>LLM.txt Settings</h3>
                                    <p className="muted">Customize how your llm.txt file is generated and what content it includes.</p>

                                    <div className="settings-form">
                                        <div className="settings-row">
                                            <div className="settings-label">
                                                <label>Title</label>
                                                <p className="settings-help">The main title displayed at the top of your llm.txt file.</p>
                                            </div>
                                            <div className="settings-control">
                                                <input
                                                    type="text"
                                                    value={llmSettings.llm_txt_title}
                                                    onChange={(e) => setLlmSettings(prev => ({ ...prev, llm_txt_title: e.target.value }))}
                                                    placeholder="Site Name"
                                                />
                                                <p className="settings-help">Defaults to your site name if left empty.</p>
                                            </div>
                                        </div>

                                        <div className="settings-row">
                                            <div className="settings-label">
                                                <label>Description</label>
                                                <p className="settings-help">A brief description of your site displayed below the title.</p>
                                            </div>
                                            <div className="settings-control">
                                                <textarea
                                                    value={llmSettings.llm_txt_description}
                                                    onChange={(e) => setLlmSettings(prev => ({ ...prev, llm_txt_description: e.target.value }))}
                                                    placeholder="Site description"
                                                    rows="3"
                                                />
                                                <p className="settings-help">Defaults to your site tagline if left empty.</p>
                                            </div>
                                        </div>

                                        <div className="settings-row">
                                            <div className="settings-label">
                                                <label>Max Posts Per Type</label>
                                                <p className="settings-help">Limit the number of posts included per post type.</p>
                                            </div>
                                            <div className="settings-control">
                                                <input
                                                    type="number"
                                                    value={llmSettings.llm_txt_posts_per_type}
                                                    onChange={(e) => setLlmSettings(prev => ({ ...prev, llm_txt_posts_per_type: parseInt(e.target.value, 10) || 50 }))}
                                                    min="1"
                                                    max="500"
                                                    style={{ width: '120px' }}
                                                />
                                                <p className="settings-help">Set between 1-500 posts per post type (recommended: 50).</p>
                                            </div>
                                        </div>

                                        <div className="settings-row">
                                            <div className="settings-label">
                                                <label>Options</label>
                                            </div>
                                            <div className="settings-control">
                                                <label className="checkbox-row">
                                                    <input
                                                        type="checkbox"
                                                        checked={llmSettings.llm_txt_include_excerpt === '1'}
                                                        onChange={(e) => setLlmSettings(prev => ({ ...prev, llm_txt_include_excerpt: e.target.checked ? '1' : '0' }))}
                                                    />
                                                    <span>Include post excerpts/descriptions in llm.txt</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                {/* Content Types Preview */}
                                <section className="panel">
                                    <h3>Content Types Included</h3>
                                    <p className="muted">The following post types will be included in your llm.txt file.</p>

                                    <div className="post-types-preview">
                                        {postTypes.map(pt => {
                                            const willInclude = Math.min(pt.count, llmSettings.llm_txt_posts_per_type);
                                            return (
                                                <div key={pt.name} className="post-type-preview-item">
                                                    <div className="post-type-icon">
                                                        <span className="dashicon">&#128196;</span>
                                                    </div>
                                                    <div className="post-type-info">
                                                        <strong>{pt.label}</strong>
                                                        <span className="muted">
                                                            Including {willInclude} of {pt.count} published posts
                                                        </span>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </section>

                                {/* Save Button */}
                                <div className="form-actions">
                                    <button
                                        type="button"
                                        className="button primary"
                                        onClick={handleSaveLlmSettings}
                                        disabled={saving}
                                    >
                                        {saving ? 'Saving...' : 'Save Changes'}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>

                    {/* Sidebar */}
                    {llmSettings.enable_llm_txt === '1' && (
                        <aside className="side-panel">
                            <div className="side-card highlight">
                                <h3>Quick Info</h3>
                                <p className="muted">Your llm.txt file is accessible at:</p>
                                <code className="url-display">{llmSettings.llm_txt_url}</code>
                                <p className="muted" style={{ marginTop: '12px' }}>
                                    Note: If the file is not accessible, visit Permalink Settings and save to flush rewrite rules.
                                </p>
                            </div>

                            <div className="side-card">
                                <h3>About llm.txt</h3>
                                <p className="muted">
                                    The llm.txt file helps AI language models like ChatGPT, Claude, and others discover
                                    and understand your content structure.
                                </p>
                                <p className="muted">
                                    This can improve how AI systems reference and cite your content when answering questions.
                                </p>
                                <a
                                    href="https://llmstxt.org/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="button ghost"
                                    style={{ marginTop: '12px' }}
                                >
                                    Learn More About llm.txt
                                </a>
                            </div>
                        </aside>
                    )}
                </div>
            )}
        </div>
    );
};

export default Sitemap;
