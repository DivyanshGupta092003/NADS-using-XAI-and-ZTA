import sys
import smtplib
from email.mime.text import MIMEText

# Read from PHP
receiver_email = sys.argv[1]
otp = sys.argv[2]

# Your Gmail details
sender_email = "abhinavjindal19here@gmail.com"
app_password = "uuzk oweb igsn xfcu"

# Create message
subject = "Your OTP Code"
body = f"Hello, your OTP is: {otp}"
msg = MIMEText(body)
msg["Subject"] = subject
msg["From"] = sender_email
msg["To"] = receiver_email

try:
    server = smtplib.SMTP("smtp.gmail.com", 587)
    server.starttls()
    server.login(sender_email, app_password)
    server.sendmail(sender_email, receiver_email, msg.as_string())
    server.quit()
    print("✅ Email sent.")
except Exception as e:
    print("❌ Email error:", e)
