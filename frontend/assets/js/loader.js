export class Loader {
    static init() {
        if (document.querySelector('.loader-overlay')) return;
        
        const loader = document.createElement('div');
        loader.className = 'loader-overlay';
        loader.innerHTML = `
            <div class="loader-spinner"></div>
            <div class="loader-text">Loading...</div>
        `;
        document.body.appendChild(loader);
    }

    static show(text = 'Loading...') {
        this.init();
        const loader = document.querySelector('.loader-overlay');
        loader.querySelector('.loader-text').innerText = text;
        loader.classList.add('active');
    }

    static hide() {
        const loader = document.querySelector('.loader-overlay');
        if (loader) {
            loader.classList.remove('active');
        }
    }
}
