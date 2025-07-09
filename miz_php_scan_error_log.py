import os
import time
import json
import requests

# Cấu hình
WATCH_DIR = "/www/web"
STATE_FILE = "/www/data/php_error_log_state.json"
TELEGRAM_TOKEN = "YOUR_BOT_TOKEN"
TELEGRAM_CHAT_ID = "YOUR_CHAT_ID"

def send_telegram_message(message):
    url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendMessage"
    data = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": message,
        "parse_mode": "Markdown"
    }
    try:
        requests.post(url, data=data, timeout=10)
    except requests.RequestException as e:
        print("Telegram error:", e)

def load_state():
    if os.path.exists(STATE_FILE):
        with open(STATE_FILE, "r") as f:
            return json.load(f)
    return {}

def save_state(state):
    with open(STATE_FILE, "w") as f:
        json.dump(state, f)

def scan_error_logs():
    current_state = {}
    for root, _, files in os.walk(WATCH_DIR):
        if "error_log" in files:
            path = os.path.join(root, "error_log")
            try:
                mtime = int(os.path.getmtime(path))
                current_state[path] = mtime
            except Exception as e:
                print("Error reading:", path, e)
    return current_state

def main():
    old_state = load_state()
    new_state = scan_error_logs()
    changes = []

    # Kiểm tra file mới hoặc bị sửa
    for path, mtime in new_state.items():
        if path not in old_state:
            changes.append(f"Phát hiện *file mới*: `{path}`")
        elif mtime != old_state[path]:
            changes.append(f"File *đã thay đổi*: `{path}`")

    if changes:
        message = "\n".join(changes)
        send_telegram_message(message)
        save_state(new_state)
        print(message)
    else:
        print("Không có thay đổi.")

if __name__ == "__main__":
    main()