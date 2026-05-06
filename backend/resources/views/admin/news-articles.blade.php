@extends('layouts.app', ['title' => 'Manage News'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Content Management</p>
        <h1 class="page-title">Manage News</h1>
        <p class="page-copy">Paste an article URL and SolMate will automatically fetch the title, description, thumbnail, and source metadata for the public Latest News section.</p>
    </div>

    <style>
        .news-admin-panel {
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .news-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .news-admin-card {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .news-admin-thumb {
            aspect-ratio: 16 / 9;
            background: linear-gradient(135deg, #EAF9FD 0%, #f7fbff 52%, #fff2c8 100%);
            overflow: hidden;
        }

        .news-admin-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .news-admin-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #5E7288;
            font-size: 13px;
            text-align: center;
            line-height: 1.6;
            background: linear-gradient(180deg, rgba(255,255,255,0.16) 0%, rgba(255,255,255,0.28) 100%);
        }

        .news-admin-card-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            flex: 1;
        }

        .news-admin-card-title {
            margin: 0;
            color: #123A5A;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
        }

        .news-admin-card-desc {
            margin: 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.7;
        }

        .news-admin-meta {
            display: grid;
            gap: 10px;
        }

        .news-admin-meta-item {
            padding: 12px 14px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
        }

        .news-admin-meta-label {
            display: block;
            margin-bottom: 4px;
            color: #7F92A3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .news-admin-meta-value {
            display: block;
            color: #123A5A;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .news-admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .news-admin-actions a,
        .news-admin-actions button {
            min-width: 120px;
        }

        .news-admin-link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 14px;
            border: 1px solid #c7d7e7;
            border-radius: 12px;
            background: #ffffff;
            color: #123A5A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .news-admin-link-btn:hover {
            text-decoration: none;
            background: #F8FAFC;
        }
    </style>

    <div class="card admin-section-surface">
        <div id="news-admin-loading" class="info-box">Loading news articles...</div>
        <div id="news-admin-success" class="status" style="display:none;"></div>
        <div id="news-admin-error" class="error-box" style="display:none;"></div>

        <div id="news-admin-content" class="stack" style="display:none;">
            <div class="news-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Add News Article</h2>
                        <div class="muted">Paste only the article URL. The system fetches metadata automatically.</div>
                    </div>
                </div>

                <form id="news-admin-form" class="form-grid two-columns" style="margin-top: 18px;">
                    <div style="grid-column: 1 / -1;">
                        <label for="news_admin_article_url">Article URL</label>
                        <input id="news_admin_article_url" name="article_url" type="url" placeholder="https://example.com/article">
                        <div class="field-error" data-news-error-for="article_url"></div>
                    </div>

                    <div class="actions" style="grid-column: 1 / -1;">
                        <button id="news-admin-submit" type="submit">Add News Article</button>
                        <span class="muted">SolMate will save title, description, thumbnail, source, and the original link.</span>
                    </div>
                </form>
            </div>

            <div class="news-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Existing Articles</h2>
                        <div class="muted">Toggle visibility, refetch metadata, or delete articles added by the admin.</div>
                    </div>
                    <button id="news-admin-refresh" type="button" class="secondary">Refresh</button>
                </div>

                <div id="news-admin-empty" class="info-box" style="display:none; margin-top: 16px;">No news articles available yet.</div>
                <div id="news-admin-list" class="news-admin-grid" style="display:none; margin-top: 16px;"></div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const loadingBox = document.getElementById('news-admin-loading');
    const successBox = document.getElementById('news-admin-success');
    const errorBox = document.getElementById('news-admin-error');
    const content = document.getElementById('news-admin-content');
    const form = document.getElementById('news-admin-form');
    const articleUrlInput = document.getElementById('news_admin_article_url');
    const submitButton = document.getElementById('news-admin-submit');
    const refreshButton = document.getElementById('news-admin-refresh');
    const emptyState = document.getElementById('news-admin-empty');
    const list = document.getElementById('news-admin-list');

    let articles = [];

    function setVisible(element, visible, displayValue = 'block') {
        element.style.display = visible ? displayValue : 'none';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return 'Not available';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
    }

    function getCookie(name) {
        const prefix = `${name}=`;

        for (const part of document.cookie.split(';')) {
            const trimmed = part.trim();

            if (trimmed.startsWith(prefix)) {
                return decodeURIComponent(trimmed.slice(prefix.length));
            }
        }

        return null;
    }

    async function ensureCsrfCookie() {
        await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    }

    async function apiRequest(endpoint, options = {}) {
        const method = options.method || 'GET';
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (method !== 'GET') {
            await ensureCsrfCookie();
            headers['Content-Type'] = 'application/json';
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }

        const response = await fetch(endpoint, {
            method,
            credentials: 'same-origin',
            headers,
            body: options.body ? JSON.stringify(options.body) : undefined,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(payload.message || 'Request failed.');
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    }

    function clearFeedback() {
        successBox.textContent = '';
        errorBox.textContent = '';
        setVisible(successBox, false);
        setVisible(errorBox, false);
        document.querySelectorAll('[data-news-error-for]').forEach((element) => {
            element.textContent = '';
        });
    }

    function showError(message) {
        errorBox.textContent = message;
        setVisible(errorBox, true);
    }

    function showSuccess(message) {
        successBox.textContent = message;
        setVisible(successBox, true);
    }

    function renderList() {
        if (!Array.isArray(articles) || articles.length === 0) {
            list.innerHTML = '';
            setVisible(list, false);
            setVisible(emptyState, true);
            return;
        }

        list.innerHTML = articles.map((article) => {
            const description = article.description || 'No description metadata was available for this article.';
            const title = article.title || article.source_name || article.article_url;
            const statusClass = article.is_active ? 'badge badge-success' : 'badge badge-warning';
            const statusLabel = article.is_active ? 'Active' : 'Inactive';
            const mediaHtml = article.thumbnail_url
                ? `<img src="${escapeHtml(article.thumbnail_url)}" alt="${escapeHtml(title)} thumbnail">`
                : '<div class="news-admin-thumb-placeholder">No thumbnail metadata available for this article yet.</div>';

            return `
                <article class="news-admin-card">
                    <div class="news-admin-thumb">${mediaHtml}</div>
                    <div class="news-admin-card-body">
                        <div class="actions" style="justify-content: space-between; align-items: flex-start; margin: 0;">
                            <span class="${statusClass}">${statusLabel}</span>
                            <span class="muted">${escapeHtml(formatDate(article.created_at))}</span>
                        </div>

                        <h3 class="news-admin-card-title">${escapeHtml(title)}</h3>
                        <p class="news-admin-card-desc">${escapeHtml(description)}</p>

                        <div class="news-admin-meta">
                            <div class="news-admin-meta-item">
                                <span class="news-admin-meta-label">Source</span>
                                <span class="news-admin-meta-value">${escapeHtml(article.source_name || 'Unknown source')}</span>
                            </div>
                            <div class="news-admin-meta-item">
                                <span class="news-admin-meta-label">Article URL</span>
                                <span class="news-admin-meta-value">${escapeHtml(article.article_url)}</span>
                            </div>
                        </div>

                        <div class="news-admin-actions">
                            <a class="news-admin-link-btn" href="${escapeHtml(article.article_url)}" target="_blank" rel="noopener noreferrer">Open Article</a>
                            <button type="button" data-news-action="toggle" data-news-id="${article.id}">${article.is_active ? 'Set Inactive' : 'Set Active'}</button>
                            <button type="button" class="secondary" data-news-action="refresh" data-news-id="${article.id}">Refresh Metadata</button>
                            <button type="button" class="danger" data-news-action="delete" data-news-id="${article.id}">Delete</button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');

        setVisible(emptyState, false);
        setVisible(list, true, 'grid');
    }

    async function loadArticles() {
        clearFeedback();
        setVisible(loadingBox, true);
        setVisible(content, false);

        try {
            const payload = await apiRequest('/api/admin/news-articles');
            articles = Array.isArray(payload.data) ? payload.data : [];
            renderList();
            setVisible(content, true);
        } catch (error) {
            showError(error.message || 'Could not load news articles.');
        } finally {
            setVisible(loadingBox, false);
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        clearFeedback();

        submitButton.disabled = true;
        submitButton.textContent = 'Adding...';

        try {
            const payload = await apiRequest('/api/admin/news-articles', {
                method: 'POST',
                body: {
                    article_url: articleUrlInput.value.trim(),
                },
            });

            form.reset();
            showSuccess(payload.message || 'News article added successfully.');
            await loadArticles();
        } catch (error) {
            if (error.status === 422 && error.payload && error.payload.errors) {
                Object.entries(error.payload.errors).forEach(([field, messages]) => {
                    const target = document.querySelector(`[data-news-error-for="${field}"]`);
                    if (target && Array.isArray(messages) && messages[0]) {
                        target.textContent = messages[0];
                    }
                });
            }

            showError(error.message || 'Could not add the news article.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Add News Article';
        }
    }

    async function toggleArticle(id) {
        clearFeedback();

        try {
            const payload = await apiRequest(`/api/admin/news-articles/${id}/toggle`, {
                method: 'PATCH',
            });
            showSuccess(payload.message || 'News article updated successfully.');
            await loadArticles();
        } catch (error) {
            showError(error.message || 'Could not update the news article.');
        }
    }

    async function refreshArticle(id) {
        clearFeedback();

        try {
            const payload = await apiRequest(`/api/admin/news-articles/${id}/refresh`, {
                method: 'POST',
            });
            showSuccess(payload.message || 'News article metadata refreshed successfully.');
            await loadArticles();
        } catch (error) {
            showError(error.message || 'Could not refresh the news article metadata.');
        }
    }

    async function deleteArticle(id) {
        if (!window.confirm('Delete this news article?')) {
            return;
        }

        clearFeedback();

        try {
            const payload = await apiRequest(`/api/admin/news-articles/${id}`, {
                method: 'DELETE',
            });
            showSuccess(payload.message || 'News article deleted successfully.');
            await loadArticles();
        } catch (error) {
            showError(error.message || 'Could not delete the news article.');
        }
    }

    form.addEventListener('submit', handleSubmit);
    refreshButton.addEventListener('click', loadArticles);

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-news-action]');

        if (!button) {
            return;
        }

        const id = Number(button.dataset.newsId);

        if (!Number.isFinite(id) || id <= 0) {
            return;
        }

        if (button.dataset.newsAction === 'toggle') {
            toggleArticle(id);
            return;
        }

        if (button.dataset.newsAction === 'refresh') {
            refreshArticle(id);
            return;
        }

        if (button.dataset.newsAction === 'delete') {
            deleteArticle(id);
        }
    });

    loadArticles();
})();
</script>
@endpush