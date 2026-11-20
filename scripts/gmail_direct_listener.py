#!/usr/bin/env python3
"""
Direct Gmail Payment Listener for AvinyaPay & GoCrazy.

Connects directly to Gmail via IMAP over SSL (imap.gmail.com:993),
checks for new Paytm payment receipts in real time (every 5 seconds),
extracts the unique Order ID and Amount, and immediately confirms the transaction.
"""

import imaplib
import email
from email.header import decode_header
import re
import json
import time
import os
import sys
import urllib.request
import urllib.error

AVINYAPAY_URL = os.environ.get("AVINYAPAY_URL", "https://avinyapay.onrender.com")
GMAIL_USER = os.environ.get("GMAIL_USER", "rootlinux09@gmail.com")
GMAIL_APP_PASSWORD = os.environ.get("GMAIL_APP_PASSWORD", "scrcgsfhyiydpfmh")

PROCESSED_FILE = os.path.join(os.path.dirname(__file__), ".processed_emails.json")

def load_processed():
    if os.path.exists(PROCESSED_FILE):
        try:
            with open(PROCESSED_FILE, "r") as f:
                return set(json.load(f))
        except Exception:
            return set()
    return set()

def save_processed(processed_set):
    try:
        with open(PROCESSED_FILE, "w") as f:
            json.dump(list(processed_set)[-500:], f)
    except Exception:
        pass

def send_signal_to_avinyapay(text: str):
    endpoint = f"{AVINYAPAY_URL.rstrip('/')}/api/v1/agent/webhook-signal"
    payload = json.dumps({
        "source": "gmail",
        "sender": "no-reply@paytm.com",
        "text": text
    }).encode("utf-8")

    req = urllib.request.Request(
        endpoint,
        data=payload,
        headers={"Content-Type": "application/json", "User-Agent": "Direct-Gmail-Agent/1.0"}
    )
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            data = json.loads(resp.read().decode())
            print(f"[{time.strftime('%X')}] AvinyaPay match response:", json.dumps(data))
            return data
    except urllib.error.HTTPError as e:
        print(f"[{time.strftime('%X')}] HTTP error {e.code}: {e.read().decode()}")
    except Exception as e:
        print(f"[{time.strftime('%X')}] Connection error: {e}")
    return None

def extract_body(msg):
    body = ""
    if msg.is_multipart():
        for part in msg.walk():
            ctype = part.get_content_type()
            cdispo = str(part.get("Content-Disposition"))
            if ctype in ("text/plain", "text/html") and "attachment" not in cdispo:
                try:
                    payload = part.get_payload(decode=True)
                    if payload:
                        charset = part.get_content_charset() or "utf-8"
                        body += " " + payload.decode(charset, errors="ignore")
                except Exception:
                    pass
    else:
        try:
            payload = msg.get_payload(decode=True)
            if payload:
                charset = msg.get_content_charset() or "utf-8"
                body = payload.decode(charset, errors="ignore")
        except Exception:
            pass
    return body

def check_gmail_once(mail, processed_ids):
    try:
        mail.select("INBOX", readonly=True)
        # Search for recent emails from Paytm
        status, data = mail.search(None, '(FROM "no-reply@paytm.com")')
        if status != "OK" or not data[0]:
            return

        msg_ids = data[0].split()
        # Check the last 10 emails
        for mid in msg_ids[-10:]:
            mid_str = mid.decode()
            if mid_str in processed_ids:
                continue

            status, msg_data = mail.fetch(mid, "(RFC822)")
            if status != "OK" or not msg_data:
                continue

            raw_email = msg_data[0][1]
            msg = email.message_from_bytes(raw_email)

            subject = ""
            for sub, enc in decode_header(msg.get("Subject", "")):
                if isinstance(sub, bytes):
                    subject += sub.decode(enc or "utf-8", errors="ignore")
                else:
                    subject += str(sub)

            body = extract_body(msg)
            full_text = f"{subject} {body}"

            # Check if this email contains an Order ID or payment notice
            if "ORD" in full_text or "Payment Received" in full_text:
                print(f"[{time.strftime('%X')}] Found Paytm payment email: {subject}")
                res = send_signal_to_avinyapay(full_text)
                if res and res.get("ok"):
                    processed_ids.add(mid_str)
                    save_processed(processed_ids)

    except imaplib.IMAP4.abort:
        raise
    except Exception as e:
        print(f"[{time.strftime('%X')}] Search check error: {e}")

def run_loop(user, password):
    processed = load_processed()
    print(f"[*] Starting Direct Gmail Listener for {user}...")
    print(f"[*] Target AvinyaPay endpoint: {AVINYAPAY_URL}/api/v1/agent/webhook-signal")

    while True:
        try:
            mail = imaplib.IMAP4_SSL("imap.gmail.com", 993)
            mail.login(user, password)
            print(f"[{time.strftime('%X')}] Connected to Gmail IMAP successfully. Listening...")

            while True:
                check_gmail_once(mail, processed)
                time.sleep(5)  # Checks every 5 seconds

        except (imaplib.IMAP4.error, imaplib.IMAP4.abort, OSError) as conn_err:
            print(f"[{time.strftime('%X')}] Connection dropped ({conn_err}), reconnecting in 5s...")
            time.sleep(5)
        except KeyboardInterrupt:
            print("\nStopped.")
            break

if __name__ == "__main__":
    pwd = GMAIL_APP_PASSWORD or (sys.argv[1] if len(sys.argv) > 1 else "")
    user = GMAIL_USER or (sys.argv[2] if len(sys.argv) > 2 else "varundevmishra01@gmail.com")

    if not pwd:
        print("Usage: python3 gmail_direct_listener.py <GMAIL_APP_PASSWORD> [EMAIL]")
        print("Or set GMAIL_APP_PASSWORD environment variable.")
        sys.exit(1)

    run_loop(user, pwd)
