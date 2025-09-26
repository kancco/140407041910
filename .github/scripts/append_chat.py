import sys
from datetime import datetime

chat_file = sys.argv[1]
md_file = sys.argv[2]

with open(chat_file, 'r', encoding='utf-8') as cfile:
    chat_text = cfile.read().strip()

if chat_text:
    with open(md_file, 'a', encoding='utf-8') as mfile:
        mfile.write('\n\n---\n\n')
        mfile.write(f'### گفتگو ثبت شده در تاریخ {datetime.now().strftime("%Y-%m-%d %H:%M")}\n\n')
        mfile.write(chat_text)
        mfile.write('\n')
    print("گفتگو به مستندات اضافه شد.")
else:
    print("فایل chat.txt خالی بود. چیزی اضافه نشد.")