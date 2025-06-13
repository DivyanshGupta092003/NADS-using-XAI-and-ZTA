import requests
import time

# Configuration
url = "http://localhost/auth.php"  # Your local site's auth handler
username = "admin"                 # Valid username (existing in your MYTAB table)
device_id = "brute_force_bot"     # Simulated attacker device

# Simulate 50 rapid failed login attempts
for i in range(50):
    fake_password = f"invalid_pass_{i}"  # Changing password every attempt
    payload = {
        "usernameInput": username,
        "passwordInput": fake_password,
        "deviceId": device_id
    }

    try:
        response = requests.post(url, data=payload)
        print(f"Attempt {i+1} | Status: {response.status_code}")
        print("Response:", response.text[:200])  # Print partial HTML response
    except Exception as e:
        print(f"Request failed on attempt {i+1}: {e}")

    time.sleep(0.3)  # Throttle slightly to simulate fast brute forcesss