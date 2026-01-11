/**
 * SEO Panel Component
 *
 * Main panel containing all SEO fields and previews.
 */

import { useState } from '@wordpress/element';
import { PanelBody, TextControl, TextareaControl, ToggleControl, Button } from '@wordpress/components';
import SearchPreview from './SearchPreview';
import ScoreGauge from './ScoreGauge';

const SEOPanel = ({
    seoMeta,
    updateMeta,
    seoScore,
    effectiveTitle,
    effectiveDescription,
    postUrl,
    postTitle,
    featuredImage,
    hasChanges,
}) => {
    const [activeTab, setActiveTab] = useState('general');

    // Character limits
    const TITLE_MAX = 60;
    const DESC_MAX = 160;

    const titleLength = (seoMeta.title || '').length;
    const descLength = (seoMeta.description || '').length;

    const getTitleStatus = () => {
        if (titleLength === 0) return 'empty';
        if (titleLength < 30) return 'short';
        if (titleLength > TITLE_MAX) return 'long';
        return 'good';
    };

    const getDescStatus = () => {
        if (descLength === 0) return 'empty';
        if (descLength < 70) return 'short';
        if (descLength > DESC_MAX) return 'long';
        return 'good';
    };

    return (
        <div className="wpseopilot-editor-panel">
            {/* Score Header */}
            <div className="wpseopilot-score-header">
                <ScoreGauge score={seoScore?.score || 0} level={seoScore?.level || 'poor'} />
                <div className="wpseopilot-score-info">
                    <div className="wpseopilot-score-label">SEO Score</div>
                    <div className="wpseopilot-score-status">
                        {seoScore?.issues?.length > 0
                            ? `${seoScore.issues.length} issue${seoScore.issues.length !== 1 ? 's' : ''} found`
                            : 'Looking good!'}
                    </div>
                </div>
            </div>

            {/* Tab Navigation */}
            <div className="wpseopilot-tabs">
                <button
                    type="button"
                    className={`wpseopilot-tab ${activeTab === 'general' ? 'active' : ''}`}
                    onClick={() => setActiveTab('general')}
                >
                    General
                </button>
                <button
                    type="button"
                    className={`wpseopilot-tab ${activeTab === 'advanced' ? 'active' : ''}`}
                    onClick={() => setActiveTab('advanced')}
                >
                    Advanced
                </button>
                <button
                    type="button"
                    className={`wpseopilot-tab ${activeTab === 'social' ? 'active' : ''}`}
                    onClick={() => setActiveTab('social')}
                >
                    Social
                </button>
            </div>

            {/* General Tab */}
            {activeTab === 'general' && (
                <div className="wpseopilot-tab-content">
                    {/* Search Preview */}
                    <div className="wpseopilot-preview-section">
                        <label className="wpseopilot-section-label">Search Preview</label>
                        <SearchPreview
                            title={effectiveTitle}
                            description={effectiveDescription}
                            url={postUrl}
                        />
                    </div>

                    {/* Focus Keyphrase */}
                    <div className="wpseopilot-field">
                        <TextControl
                            label="Focus Keyphrase"
                            value={seoMeta.focus_keyphrase}
                            onChange={(value) => updateMeta('focus_keyphrase', value)}
                            placeholder="Enter your target keyword"
                            help="The main keyword you want this page to rank for"
                        />
                    </div>

                    {/* SEO Title */}
                    <div className="wpseopilot-field">
                        <div className="wpseopilot-field-header">
                            <label>SEO Title</label>
                            <span className={`wpseopilot-char-count ${getTitleStatus()}`}>
                                {titleLength}/{TITLE_MAX}
                            </span>
                        </div>
                        <TextControl
                            value={seoMeta.title}
                            onChange={(value) => updateMeta('title', value)}
                            placeholder={postTitle || 'Enter SEO title'}
                        />
                        <div className="wpseopilot-progress-bar">
                            <div
                                className={`wpseopilot-progress-fill ${getTitleStatus()}`}
                                style={{ width: `${Math.min((titleLength / TITLE_MAX) * 100, 100)}%` }}
                            />
                        </div>
                    </div>

                    {/* Meta Description */}
                    <div className="wpseopilot-field">
                        <div className="wpseopilot-field-header">
                            <label>Meta Description</label>
                            <span className={`wpseopilot-char-count ${getDescStatus()}`}>
                                {descLength}/{DESC_MAX}
                            </span>
                        </div>
                        <TextareaControl
                            value={seoMeta.description}
                            onChange={(value) => updateMeta('description', value)}
                            placeholder="Enter meta description"
                            rows={3}
                        />
                        <div className="wpseopilot-progress-bar">
                            <div
                                className={`wpseopilot-progress-fill ${getDescStatus()}`}
                                style={{ width: `${Math.min((descLength / DESC_MAX) * 100, 100)}%` }}
                            />
                        </div>
                    </div>

                    {/* Quick Analysis */}
                    {seoScore?.issues?.length > 0 && (
                        <div className="wpseopilot-issues">
                            <label className="wpseopilot-section-label">Analysis</label>
                            <ul className="wpseopilot-issues-list">
                                {seoScore.issues.slice(0, 5).map((issue, idx) => (
                                    <li key={idx} className={`wpseopilot-issue wpseopilot-issue--${issue.severity || 'warning'}`}>
                                        <span className="wpseopilot-issue-icon">
                                            {issue.severity === 'error' ? '!' : '?'}
                                        </span>
                                        <span className="wpseopilot-issue-text">{issue.message}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            )}

            {/* Advanced Tab */}
            {activeTab === 'advanced' && (
                <div className="wpseopilot-tab-content">
                    {/* Canonical URL */}
                    <div className="wpseopilot-field">
                        <TextControl
                            label="Canonical URL"
                            value={seoMeta.canonical}
                            onChange={(value) => updateMeta('canonical', value)}
                            placeholder={postUrl}
                            help="Leave empty to use the default URL"
                            type="url"
                        />
                    </div>

                    {/* Robots Settings */}
                    <div className="wpseopilot-robots-section">
                        <label className="wpseopilot-section-label">Search Engine Visibility</label>

                        <ToggleControl
                            label="Hide from search results"
                            help="Add noindex meta tag to prevent indexing"
                            checked={seoMeta.noindex}
                            onChange={(value) => updateMeta('noindex', value)}
                        />

                        <ToggleControl
                            label="Don't follow links"
                            help="Add nofollow meta tag to links on this page"
                            checked={seoMeta.nofollow}
                            onChange={(value) => updateMeta('nofollow', value)}
                        />
                    </div>

                    {/* Robots Preview */}
                    <div className="wpseopilot-robots-preview">
                        <label className="wpseopilot-section-label">Robots Meta</label>
                        <code className="wpseopilot-robots-code">
                            {seoMeta.noindex || seoMeta.nofollow
                                ? `${seoMeta.noindex ? 'noindex' : 'index'}, ${seoMeta.nofollow ? 'nofollow' : 'follow'}`
                                : 'index, follow (default)'}
                        </code>
                    </div>
                </div>
            )}

            {/* Social Tab */}
            {activeTab === 'social' && (
                <div className="wpseopilot-tab-content">
                    {/* Social Preview */}
                    <div className="wpseopilot-social-preview">
                        <label className="wpseopilot-section-label">Social Preview</label>
                        <div className="wpseopilot-social-card">
                            <div className="wpseopilot-social-image">
                                {seoMeta.og_image || featuredImage ? (
                                    <img src={seoMeta.og_image || featuredImage} alt="" />
                                ) : (
                                    <div className="wpseopilot-social-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" strokeWidth="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                            <path d="M21 15l-5-5L5 21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                        </svg>
                                        <span>No image set</span>
                                    </div>
                                )}
                            </div>
                            <div className="wpseopilot-social-content">
                                <div className="wpseopilot-social-url">{new URL(postUrl).hostname}</div>
                                <div className="wpseopilot-social-title">{effectiveTitle}</div>
                                <div className="wpseopilot-social-desc">{effectiveDescription || 'No description available'}</div>
                            </div>
                        </div>
                    </div>

                    {/* OG Image */}
                    <div className="wpseopilot-field">
                        <TextControl
                            label="Social Image URL"
                            value={seoMeta.og_image}
                            onChange={(value) => updateMeta('og_image', value)}
                            placeholder="https://..."
                            help="Override the featured image for social sharing (1200x630 recommended)"
                            type="url"
                        />
                        {!seoMeta.og_image && featuredImage && (
                            <p className="wpseopilot-field-note">
                                Using featured image as fallback
                            </p>
                        )}
                    </div>

                    {/* Media Library Button */}
                    <Button
                        variant="secondary"
                        className="wpseopilot-media-button"
                        onClick={() => {
                            const frame = wp.media({
                                title: 'Select Social Image',
                                button: { text: 'Use Image' },
                                multiple: false,
                            });
                            frame.on('select', () => {
                                const attachment = frame.state().get('selection').first().toJSON();
                                updateMeta('og_image', attachment.url);
                            });
                            frame.open();
                        }}
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style={{ marginRight: '6px' }}>
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" strokeWidth="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                            <path d="M21 15l-5-5L5 21" stroke="currentColor" strokeWidth="2"/>
                        </svg>
                        Select Image
                    </Button>
                </div>
            )}
        </div>
    );
};

export default SEOPanel;
