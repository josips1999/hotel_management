<!DOCTYPE html>
<html>
<head>
    <title>Login Debug</title>
</head>
<body>
    <h2>Login Test with Full Error Details</h2>
    
    <form id="loginForm">
        <label>Email:</label><br>
        <input type="text" id="email" value="jskoko53@gmail.com"><br><br>
        
        <label>Password:</label><br>
        <input type="password" id="password" value="password123"><br><br>
        
        <button type="submit">Test Login</button>
    </form>
    
    <h3>Response:</h3>
    <pre id="result"></pre>
    
    <script>
        // Get CSRF token first
        fetch('lib/CSRFToken.php')
            .then(r => r.text())
            .then(token => {
                console.log('CSRF Token:', token);
            });
        
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('usernameOrEmail', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('rememberMe', '0');
            formData.append('g-recaptcha-response', 'localhost-bypass');
            
            // Get CSRF token from session
            const csrfResponse = await fetch('get_csrf_token.php');
            const csrfToken = await csrfResponse.text();
            formData.append('csrf_token', csrfToken.trim());
            
            console.log('Sending login request...');
            
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                document.getElementById('result').textContent = text;
                console.log('Response:', text);
            } catch (error) {
                document.getElementById('result').textContent = 'Error: ' + error.message;
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
