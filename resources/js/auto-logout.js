class AutoLogout {
    constructor() {
        this.timeout = null;
        this.warningTime = 5 * 60 * 1000; // 5 menit sebelum logout
        this.logoutTime = parseInt(document.querySelector('meta[name="session-lifetime"]')?.content || 120) * 60 * 1000;
        this.events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupTimeout();
        this.setupVisibilityChange();
    }

    setupEventListeners() {
        this.events.forEach(event => {
            document.addEventListener(event, () => this.resetTimer());
        });
    }

    setupVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.resetTimer();
            }
        });
    }

    resetTimer() {
        clearTimeout(this.timeout);
        this.setupTimeout();
        this.hideWarning();
    }

    setupTimeout() {
        // Set warning timeout
        this.timeout = setTimeout(() => this.showWarning(), this.logoutTime - this.warningTime);
        
        // Set logout timeout
        this.timeout = setTimeout(() => this.logout(), this.logoutTime);
    }

    showWarning() {
        // Create warning modal
        const modal = this.createWarningModal();
        document.body.appendChild(modal);
        
        // Start countdown
        this.startCountdown();
    }

    createWarningModal() {
        const modal = document.createElement('div');
        modal.id = 'auto-logout-warning';
        modal.innerHTML = `
            <div class="auto-logout-overlay">
                <div class="auto-logout-modal">
                    <div class="auto-logout-header">
                        <h4>⚠️ Session Akan Berakhir</h4>
                    </div>
                    <div class="auto-logout-body">
                        <p>Session Anda akan berakhir dalam <span id="countdown">5:00</span> menit karena tidak ada aktivitas.</p>
                        <p>Klik "Lanjutkan" untuk memperpanjang session.</p>
                    </div>
                    <div class="auto-logout-footer">
                        <button id="continue-session" class="btn btn-primary">Lanjutkan</button>
                        <button id="logout-now" class="btn btn-secondary">Logout Sekarang</button>
                    </div>
                </div>
            </div>
        `;

        // Event listeners untuk buttons
        modal.querySelector('#continue-session').addEventListener('click', () => {
            this.resetTimer();
            this.hideWarning();
        });

        modal.querySelector('#logout-now').addEventListener('click', () => {
            this.logout();
        });

        return modal;
    }

    startCountdown() {
        let timeLeft = this.warningTime / 1000; // Convert ke detik
        const countdownElement = document.getElementById('countdown');

        const countdown = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(countdown);
                this.logout();
            }
        }, 1000);
    }

    hideWarning() {
        const warning = document.getElementById('auto-logout-warning');
        if (warning) {
            warning.remove();
        }
    }

    logout() {
        // Show logout confirmation
        if (confirm('Session Anda telah berakhir. Anda akan dialihkan ke halaman login.')) {
            window.location.href = '/logout';
        } else {
            window.location.href = '/logout';
        }
    }

    destroy() {
        this.events.forEach(event => {
            document.removeEventListener(event, () => this.resetTimer());
        });
        clearTimeout(this.timeout);
    }
}

// Initialize auto logout
document.addEventListener('DOMContentLoaded', function() {
    window.autoLogout = new AutoLogout();
});