import { Controller } from '@hotwired/stimulus';

const KEYS = {
    content: 'fws-theme',
    sidebar: 'fws-sidebar-theme'
};

const DEFAULTS = {
    content: 'system',
    sidebar: 'auto'
};

const read = key => {
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
};

const write = (key, value) => {
    try {
        localStorage.setItem(key, value);
    } catch {
    }
};

/**
 * Color mode switcher. The content theme (light / dark / system) is set as data-bs-theme on <html>,
 * the sidebar theme (light / dark / auto = like content) as data-fws-sidebar on <html>.
 *
 *   <button data-action="theme#set" data-theme-area-param="content" data-theme-value-param="dark">
 */
export default class extends Controller {
    static targets = ['option'];

    connect() {
        this.media = window.matchMedia('(prefers-color-scheme: dark)');
        this.onSystemChange = () => this.apply();
        this.media.addEventListener('change', this.onSystemChange);
        this.apply();
    }

    disconnect() {
        this.media.removeEventListener('change', this.onSystemChange);
    }

    set({ params: { area, value } }) {
        write(KEYS[area], value);
        this.apply();
    }

    apply() {
        const root = document.documentElement;
        const content = read(KEYS.content) ?? DEFAULTS.content;
        const sidebar = read(KEYS.sidebar) ?? DEFAULTS.sidebar;
        const isDark = content === 'dark' || (content === 'system' && this.media.matches);

        root.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

        if (sidebar === 'light' || sidebar === 'dark') {
            root.setAttribute('data-fws-sidebar', sidebar);
        } else {
            root.removeAttribute('data-fws-sidebar');
        }

        const current = { content, sidebar };
        this.optionTargets.forEach(option => {
            const isActive = current[option.dataset.themeAreaParam] === option.dataset.themeValueParam;
            option.classList.toggle('active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }
}
