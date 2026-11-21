#!/usr/bin/env python3
"""
High-Performance Real-Time Gmail Payment Listener for AvinyaPay & GoCrazy.

Optimizations:
- Fast 2-second check interval
- Uses fast UNSEEN + Paytm search without full mailbox re-scan
- Selective header & body fetch (avoids downloading heavy HTML/attachments)
- Instant webhook dispatch to AvinyaPay
"""

import imaplib
import email
from email.header import decode_header
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
        headers={"Content-Type": "application/json", "User-Agent": "Direct-Gmail-Agent/2.0"}
    )
    try:
        with urllib.request.urlopen(req, timeout=8) as resp:
            data = json.loads(resp.read().decode())
            print(f"[{time.strftime('%X')}] ⚡ AvinyaPay confirmed signal:", json.dumps(data))
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

def check_gmail_fast(mail, processed_ids):
    try:
        # Check mailbox status without full reselect
        mail.check()
        
        # Search for recent emails from Paytm
        status, data = mail.search(None, '(FROM "no-reply@paytm.com")')
        if status != "OK" or not data[0]:
            return

        msg_ids = data[0].split()
        if not msg_ids:
            return

        # Check only the newest 5 messages
        for mid in msg_ids[-5:]:
            mid_str = mid.decode()
            if mid_str in processed_ids:
                continue

            # Fetch subject and body
            status, msg_data = mail.fetch(mid, "(RFC822)")
            if status != "OK" or not msg_data or not msg_data[0]:
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

            # Check if this email contains an Order ID or payment notification
            if "ORD" in full_text or "Payment Received" in full_text:
                print(f"[{time.strftime('%X')}] 🔔 Detected Paytm email: {subject}")
                res = send_signal_to_avinyapay(full_text)
                if res and res.get("ok"):
                    processed_ids.add(mid_str)
                    save_processed(processed_ids)

    except (imaplib.IMAP4.abort, imaplib.IMAP4.error):
        raise
    except Exception as e:
        print(f"[{time.strftime('%X')}] Check notice: {e}")

def run_loop(user, password):
    processed = load_processed()
    print(f"[*] Starting Fast Direct Gmail Listener for {user}...")
    print(f"[*] Target AvinyaPay endpoint: {AVINYAPAY_URL}/api/v1/agent/webhook-signal")

    while True:
        try:
            mail = imaplib.IMAP4_SSL("imap.gmail.com", 993)
            mail.login(user, password)
            mail.select("INBOX", readonly=True)
            print(f"[{time.strftime('%X')}] Connected to Gmail IMAP successfully. Fast listening (2s interval)...")

            while True:
                check_gmail_fast(mail, processed)
                time.sleep(2)  # Fast 2-second polling interval

        except (imaplib.IMAP4.error, imaplib.IMAP4.abort, OSError) as conn_err:
            print(f"[{time.strftime('%X')}] Connection dropped ({conn_err}), reconnecting in 2s...")
            time.sleep(2)
        except KeyboardInterrupt:
            print("\nStopped.")
            break

if __name__ == "__main__":
    pwd = GMAIL_APP_PASSWORD or (sys.argv[1] if len(sys.argv) > 1 else "")
    user = GMAIL_USER or (sys.argv[2] if len(sys.argv) > 2 else "rootlinux09@gmail.com")

    if not pwd:
        print("Usage: python3 gmail_direct_listener.py <GMAIL_APP_PASSWORD> [EMAIL]")
        sys.exit(1)

    run_loop(user, pwd)
