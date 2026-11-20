#!/usr/bin/env python3
"""
Gmail Payment Signal Extraction Agent for AvinyaPay.

Listens to or polls Gmail for payment credit alerts (e.g. from no-reply@paytm.com,
bank alert emails, UPI credit notices), extracts Amount and UTR/Order ID,
and sends the signal to AvinyaPay's verification agent.
"""

import sys
import os
import re
import json
import urllib.request
import urllib.error

AVINYAPAY_URL = os.environ.get("AVINYAPAY_URL", "https://avinyapay.onrender.com")

def extract_payment_info(subject: str, body: str):
    full_text = f"{subject} {body}"
    info = {"amount": None, "utr": None, "order_id": None}

    # Extract amount
    amt_match = re.search(r'(?:rs\.?|inr|₹|received|credited)\s*[:#-]?\s*([0-9]+(?:\.[0-9]{1,2})?)', full_text, re.IGNORECASE)
    if amt_match:
        info["amount"] = float(amt_match.group(1))

    # Extract 12-digit UTR
    utr_match = re.search(r'(?:utr|rrn|ref(?:erence)?\s*(?:no|num|id)?|txn\s*(?:id)?|transaction\s*id)[\s:#-]+([0-9]{12})\b', full_text, re.IGNORECASE)
    if not utr_match:
        utr_match = re.search(r'\b([0-9]{12})\b', full_text)
    if utr_match:
        info["utr"] = utr_match.group(1)

    # Extract Order ID
    order_match = re.search(r'\b(ORD[A-Z0-9]{14,24}|gc_[a-zA-Z0-9_]{6,24})\b', full_text)
    if order_match:
        info["order_id"] = order_match.group(1)

    return info

def send_signal(text: str, source: str = "gmail", sender: str = "no-reply@paytm.com"):
    endpoint = f"{AVINYAPAY_URL.rstrip('/')}/api/v1/agent/webhook-signal"
    payload = json.dumps({
        "text": text,
        "source": source,
        "sender": sender
    }).encode("utf-8")

    req = urllib.request.Request(
        endpoint,
        data=payload,
        headers={"Content-Type": "application/json", "User-Agent": "AvinyaPay-Agent/1.0"}
    )
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            res_data = json.loads(resp.read().decode())
            print("Agent Signal Response:", json.dumps(res_data, indent=2))
            return res_data
    except urllib.error.HTTPError as e:
        print(f"HTTP Error {e.code}: {e.read().decode()}")
    except Exception as e:
        print(f"Error sending signal: {e}")
    return None

if __name__ == "__main__":
    if len(sys.argv) > 1:
        raw_text = " ".join(sys.argv[1:])
        send_signal(raw_text)
    else:
        print("Usage: python3 gmail_signal_agent.py '<email text or notification>'")
