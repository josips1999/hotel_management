<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
</head>
<body>
    <h2>Test Prijave</h2>
    
    <form id="loginForm">
        <label>Username/Email:</label><br>
        <input type="text" id="username" value="josip_skoko"><br><br>
        
        <label>Password:</label><br>
        <input type="text" id="password" value="abc123"><br><br>
        
        <label>
            <input type="checkbox" id="rememberMe">
            Remember Me
        </label><br><br>
        
        <button type="submit">Prijavi se</button>
    </form>
    
    <h3>Rezultat:</h3>
    <pre id="result"></pre>
    
    <h3>Provjera hashovane lozinke:</h3>
    <pre id="hashCheck"></pre>
    
    <script>
        // Check password hash first
        fetch('check_password.php?username=josip_skoko&password=abc123')
            .then(r => r.text())
            .then(text => {
                document.getElementById('hashCheck').textContent = text;
            });
        
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('usernameOrEmail', document.getElementById('username').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('rememberMe', document.getElementById('rememberMe').checked ? '1' : '0');
            formData.append('g-recaptcha-response', 'test');
            formData.append('csrf_token', 'test');
            
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                document.getElementById('result').textContent = text;
            } catch (error) {
                document.getElementById('result').textContent = 'Error: ' + error.message;
            }
        });
    </script>
</body>
</html>
