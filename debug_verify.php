<!DOCTYPE html>
<html>
<head>
    <title>Debug Verify</title>
</head>
<body>
    <h2>Test Verifikacije</h2>
    <form id="testForm">
        <label>Email:</label><br>
        <input type="email" id="email" value="ivanaviigo@gmail.com"><br><br>
        
        <label>Kod:</label><br>
        <input type="text" id="code" value="067668"><br><br>
        
        <button type="submit">Testiraj</button>
    </form>
    
    <h3>Rezultat:</h3>
    <pre id="result"></pre>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('email', document.getElementById('email').value);
            formData.append('code', document.getElementById('code').value);
            
            try {
                const response = await fetch('api/verify_code.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                document.getElementById('result').textContent = text;
                
                console.log('Response:', text);
            } catch (error) {
                document.getElementById('result').textContent = 'Error: ' + error.message;
            }
        });
    </script>
</body>
</html>
