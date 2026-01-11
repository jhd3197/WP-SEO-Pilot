const More = () => {
    return (
        <div className="page">
            <div className="page-header">
                <div>
                    <h1>More from Pilot</h1>
                    <p>Expand your WordPress toolkit with trusted companion plugins from the Pilot family.</p>
                </div>
                <a
                    href="https://github.com/jhd3197"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="button ghost"
                >
                    View All Plugins
                </a>
            </div>
            <div className="pilot-grid">
                {/* WP SEO Pilot - Current Plugin */}
                <div className="pilot-card active">
                    <div className="pilot-card-head">
                        <div className="pilot-card-identity">
                            <span className="pilot-card-mark seo" aria-hidden="true">
                                <svg viewBox="0 0 24 24" role="img" focusable="false">
                                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                            </span>
                            <div>
                                <div className="pilot-card-title">
                                    <h3>WP SEO Pilot</h3>
                                    <span className="badge success">Installed</span>
                                </div>
                                <p className="pilot-card-tagline">Performance-led SEO insights.</p>
                            </div>
                        </div>
                        <label className="toggle">
                            <input type="checkbox" defaultChecked />
                            <span className="toggle-track" />
                            <span className="toggle-text">Enabled</span>
                        </label>
                    </div>
                    <p className="pilot-card-desc">Actionable SEO guidance, audits, sitemaps, redirects, and ranking insights.</p>
                    <div className="pilot-card-meta">
                        <span className="pill success">Active</span>
                        <a
                            href="https://github.com/jhd3197/WP-SEO-Pilot"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="pilot-card-link"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.6 2.9 8.5 6.9 9.9.5.1.7-.2.7-.5v-1.9c-2.8.6-3.3-1.2-3.3-1.2-.5-1.2-1.2-1.5-1.2-1.5-1-.7.1-.7.1-.7 1.1.1 1.7 1.2 1.7 1.2 1 .1.7 1.7 2.6 1.2.1-.8.4-1.2.7-1.5-2.2-.2-4.5-1.2-4.5-5.2 0-1.1.4-2 1-2.7-.1-.2-.4-1.3.1-2.7 0 0 .8-.2 2.7 1a9.2 9.2 0 0 1 4.9 0c1.9-1.2 2.7-1 2.7-1 .5 1.4.2 2.5.1 2.7.6.7 1 1.6 1 2.7 0 4-2.3 5-4.5 5.2.4.3.8 1 .8 2.1v3c0 .3.2.6.7.5 4-1.4 6.9-5.3 6.9-9.9C22 6.6 17.5 2 12 2z"/>
                            </svg>
                            GitHub
                        </a>
                    </div>
                </div>

                {/* WP AI Pilot - New AI Management Plugin */}
                <div className="pilot-card">
                    <div className="pilot-card-head">
                        <div className="pilot-card-identity">
                            <span className="pilot-card-mark ai" aria-hidden="true">
                                <svg viewBox="0 0 24 24" role="img" focusable="false">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                    <circle cx="12" cy="12" r="1"/>
                                    <circle cx="8" cy="12" r="1"/>
                                    <circle cx="16" cy="12" r="1"/>
                                </svg>
                            </span>
                            <div>
                                <div className="pilot-card-title">
                                    <h3>WP AI Pilot</h3>
                                    <span className="badge">Available</span>
                                </div>
                                <p className="pilot-card-tagline">Centralized AI management.</p>
                            </div>
                        </div>
                    </div>
                    <p className="pilot-card-desc">A unified AI interface for WordPress. Manage models, track usage, and let all your plugins leverage AI through a single hub.</p>
                    <div className="pilot-card-meta">
                        <span className="pill warning">Not Installed</span>
                        <a
                            href="https://github.com/jhd3197/WP-AI-Pilot"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="button primary"
                        >
                            Get Plugin
                        </a>
                    </div>
                </div>

                {/* WP Security Pilot */}
                <div className="pilot-card">
                    <div className="pilot-card-head">
                        <div className="pilot-card-identity">
                            <span className="pilot-card-mark security" aria-hidden="true">
                                <svg viewBox="0 0 24 24" role="img" focusable="false">
                                    <path d="M12 2L4 5.4v6.2c0 5.1 3.4 9.7 8 10.4 4.6-.7 8-5.3 8-10.4V5.4L12 2zm0 2.2l6 2.3v5.1c0 4-2.5 7.6-6 8.3-3.5-.7-6-4.3-6-8.3V6.5l6-2.3z" />
                                    <path d="M10.5 12.7l-2-2-1.3 1.3 3.3 3.3 5.3-5.3-1.3-1.3-4 4z" />
                                </svg>
                            </span>
                            <div>
                                <div className="pilot-card-title">
                                    <h3>WP Security Pilot</h3>
                                    <span className="badge">Available</span>
                                </div>
                                <p className="pilot-card-tagline">Open standard security.</p>
                            </div>
                        </div>
                    </div>
                    <p className="pilot-card-desc">Core security suite with firewall, malware scans, login protection, and hardening controls.</p>
                    <div className="pilot-card-meta">
                        <span className="pill warning">Not Installed</span>
                        <a
                            href="https://github.com/jhd3197/WP-Security-Pilot"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="button primary"
                        >
                            Get Plugin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default More;
