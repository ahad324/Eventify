export class API {
    static BASE_URL = window.location.pathname.includes('/admin/') 
        ? '../../backend/api/index.php' 
        : '../backend/api/index.php';

    static async get(resource, params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = `${this.BASE_URL}/${resource}${query ? '?' + query : ''}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('API request failed');
        return await response.json();
    }

    static async post(resource, data) {
        const response = await fetch(`${this.BASE_URL}/${resource}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('API request failed');
        return await response.json();
    }

    static async delete(resource, params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = `${this.BASE_URL}/${resource}${query ? '?' + query : ''}`;
        
        const response = await fetch(url, { method: 'DELETE' });
        if (!response.ok) throw new Error('API request failed');
        return await response.json();
    }
}
