import { Loader } from './loader.js';

export class API {
    static get ROOT_URL() {
        const path = window.location.pathname;
        const index = path.indexOf('/frontend');
        if (index === -1) return ''; // Fallback
        return path.substring(0, index);
    }

    static get BASE_URL() {
        return `${this.ROOT_URL}/backend/api/index.php`;
    }

    static async get(resource, params = {}) {
        Loader.show();
        try {
            const query = new URLSearchParams(params).toString();
            const url = `${this.BASE_URL}/${resource}${query ? '?' + query : ''}`;
            
            const response = await fetch(url, { credentials: 'include' });
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'API request failed');
            }
            return await response.json();
        } finally {
            Loader.hide();
        }
    }

    static async post(resource, data) {
        Loader.show();
        try {
            const isFormData = data instanceof FormData;
            const options = {
                method: 'POST',
                body: isFormData ? data : JSON.stringify(data),
                credentials: 'include'
            };

            if (!isFormData) {
                options.headers = { 'Content-Type': 'application/json' };
            }

            const response = await fetch(`${this.BASE_URL}/${resource}`, options);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'API request failed');
            }
            return await response.json();
        } finally {
            Loader.hide();
        }
    }

    static async delete(resource, params = {}) {
        Loader.show();
        try {
            const query = new URLSearchParams(params).toString();
            const url = `${this.BASE_URL}/${resource}${query ? '?' + query : ''}`;
            
            const response = await fetch(url, { 
                method: 'DELETE',
                credentials: 'include'
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'API request failed');
            }
            return await response.json();
        } finally {
            Loader.hide();
        }
    }
}
